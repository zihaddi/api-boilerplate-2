<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class PaymentService
{
    protected array $gatewayBindings = [
        Payment::GATEWAY_STRIPE => StripeGateway::class,
        Payment::GATEWAY_PAYPAL => PayPalGateway::class,
        Payment::GATEWAY_SSLCOMMERZ => SSLCommerzGateway::class,
    ];

    protected array $gatewayCache = [];

    public function getGateway(string $gateway): PaymentGatewayInterface
    {
        if (!isset($this->gatewayBindings[$gateway])) {
            throw new Exception("Unsupported payment gateway: {$gateway}");
        }

        if (!isset($this->gatewayCache[$gateway])) {
            $this->gatewayCache[$gateway] = App::make($this->gatewayBindings[$gateway]);
        }

        return $this->gatewayCache[$gateway];
    }

    public function getAvailableGateways(): array
    {
        return array_keys($this->gatewayBindings);
    }

    public function registerGateway(string $name, string $class): void
    {
        $this->gatewayBindings[$name] = $class;
    }

    public function initiatePayment(array $data): array
    {
        $gateway = $data['gateway'] ?? Payment::GATEWAY_STRIPE;
        $gatewayService = $this->getGateway($gateway);

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'transaction_id' => Str::uuid(),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'gateway' => $gateway,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'status' => Payment::STATUS_PENDING,
                'payment_type' => $data['payment_type'] ?? Payment::TYPE_ONE_TIME,
                'payable_type' => $data['payable_type'] ?? null,
                'payable_id' => $data['payable_id'] ?? null,
                'description' => $data['description'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'ip_address' => request()->ip(),
                'metadata' => $data['metadata'] ?? null,
            ]);

            $gatewayData = array_merge($data, [
                'transaction_id' => $payment->transaction_id,
            ]);

            $result = $gatewayService->createPaymentIntent($gatewayData);

            if ($result['success']) {
                $payment->update([
                    'gateway_transaction_id' => $result['payment_intent_id'] ?? $result['order_id'] ?? null,
                    'status' => Payment::STATUS_PROCESSING,
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'payment' => $payment,
                    'gateway_response' => $result,
                ];
            }

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => $result,
            ]);

            DB::commit();

            return [
                'success' => false,
                'payment' => $payment,
                'error' => $result['error'] ?? 'Payment initiation failed',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Payment initiation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmPayment(string $transactionId, string $gatewayPaymentId = null): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $gatewayService = $this->getGateway($payment->gateway);
        $paymentId = $gatewayPaymentId ?? $payment->gateway_transaction_id;

        try {
            $result = $gatewayService->confirmPayment($paymentId);

            if ($result['success']) {
                $payment->markAsCompleted();
                $payment->update([
                    'gateway_response' => $result,
                    'net_amount' => $payment->calculateNetAmount(),
                ]);

                return [
                    'success' => true,
                    'payment' => $payment->fresh(),
                    'message' => 'Payment confirmed successfully',
                ];
            }

            $payment->markAsFailed($result['error'] ?? 'Confirmation failed');
            $payment->update(['gateway_response' => $result]);

            return [
                'success' => false,
                'payment' => $payment->fresh(),
                'error' => $result['error'] ?? 'Payment confirmation failed',
            ];
        } catch (Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(string $transactionId, float $amount = null, string $reason = null): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        if (!$payment->isCompleted()) {
            return ['success' => false, 'error' => 'Only completed payments can be refunded'];
        }

        $gatewayService = $this->getGateway($payment->gateway);

        try {
            $result = $gatewayService->refundPayment(
                $payment->gateway_transaction_id,
                $amount ?? $payment->amount
            );

            if ($result['success']) {
                $payment->markAsRefunded($amount ?? $payment->amount, $reason);

                return [
                    'success' => true,
                    'payment' => $payment->fresh(),
                    'refund' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'] ?? 'Refund failed',
            ];
        } catch (Exception $e) {
            Log::error('Payment refund failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $transactionId): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $gatewayService = $this->getGateway($payment->gateway);

        try {
            $gatewayDetails = $gatewayService->getPaymentDetails($payment->gateway_transaction_id);

            return [
                'success' => true,
                'payment' => $payment,
                'gateway_details' => $gatewayDetails,
            ];
        } catch (Exception $e) {
            return [
                'success' => true,
                'payment' => $payment,
                'gateway_details' => null,
                'warning' => 'Could not fetch gateway details',
            ];
        }
    }

    public function handleWebhook(string $gateway, array $payload, string $signature = null): array
    {
        $gatewayService = $this->getGateway($gateway);

        $result = $gatewayService->handleWebhook($payload);

        if (isset($result['payment_intent_id']) || isset($result['transaction_id']) || isset($result['order_id'])) {
            $gatewayPaymentId = $result['payment_intent_id'] 
                ?? $result['transaction_id'] 
                ?? $result['order_id'];

            $payment = Payment::where('gateway_transaction_id', $gatewayPaymentId)->first();

            if ($payment) {
                switch ($result['status'] ?? null) {
                    case 'completed':
                        $payment->markAsCompleted();
                        break;
                    case 'failed':
                        $payment->markAsFailed($result['error'] ?? null);
                        break;
                    case 'refunded':
                        $payment->markAsRefunded($result['amount'] ?? null);
                        break;
                }
            }
        }

        return $result;
    }

    public function getPaymentsByUser(int $userId, array $filters = []): array
    {
        $query = Payment::where('user_id', $userId)
            ->filter($filters)
            ->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15)->toArray();
    }

    public function getPaymentStats(array $filters = []): array
    {
        $query = Payment::query()->filter($filters);

        return [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'completed_payments' => $query->clone()->completed()->count(),
            'completed_amount' => $query->clone()->completed()->sum('amount'),
            'pending_payments' => $query->clone()->pending()->count(),
            'failed_payments' => $query->clone()->failed()->count(),
            'by_gateway' => $query->clone()
                ->selectRaw('gateway, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('gateway')
                ->get()
                ->toArray(),
        ];
    }
}
