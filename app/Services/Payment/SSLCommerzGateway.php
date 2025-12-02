<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSLCommerzGateway implements PaymentGatewayInterface
{
    protected string $storeId;
    protected string $storePassword;
    protected string $baseUrl;
    protected bool $sandboxMode;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->storeId = config('payment.gateways.sslcommerz.store_id', '');
        $this->storePassword = config('payment.gateways.sslcommerz.store_password', '');
        $this->sandboxMode = (bool) config('payment.gateways.sslcommerz.sandbox_mode', true);
        $this->baseUrl = config('payment.gateways.sslcommerz.api_domain', 'https://sandbox.sslcommerz.com');
        $this->isConfigured = !empty($this->storeId) && !empty($this->storePassword);
    }

    protected function ensureConfigured(): void
    {
        if (!$this->isConfigured) {
            throw new \Exception('SSLCommerz gateway is not configured. Please set SSLCZ_STORE_ID and SSLCZ_STORE_PASSWORD environment variables.');
        }
    }

    protected function getUrl(string $path): string
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return config('app.url', 'http://localhost') . $path;
        }
        return url($path);
    }

    public function createPaymentIntent(array $data): array
    {
        $this->ensureConfigured();
        try {
            $postData = [
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'total_amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BDT',
                'tran_id' => $data['transaction_id'] ?? uniqid('SSLCZ_'),
                'success_url' => $data['success_url'] ?? $this->getUrl(config('payment.gateways.sslcommerz.success_url', '/api/payments/sslcommerz/success')),
                'fail_url' => $data['fail_url'] ?? $this->getUrl(config('payment.gateways.sslcommerz.fail_url', '/api/payments/sslcommerz/fail')),
                'cancel_url' => $data['cancel_url'] ?? $this->getUrl(config('payment.gateways.sslcommerz.cancel_url', '/api/payments/sslcommerz/cancel')),
                'ipn_url' => $data['ipn_url'] ?? $this->getUrl(config('payment.gateways.sslcommerz.ipn_url', '/api/payments/sslcommerz/ipn')),
                'cus_name' => $data['customer_name'] ?? 'Customer',
                'cus_email' => $data['customer_email'] ?? 'customer@example.com',
                'cus_add1' => $data['customer_address'] ?? 'N/A',
                'cus_city' => $data['customer_city'] ?? 'N/A',
                'cus_postcode' => $data['customer_postcode'] ?? '0000',
                'cus_country' => $data['customer_country'] ?? 'Bangladesh',
                'cus_phone' => $data['customer_phone'] ?? '01700000000',
                'shipping_method' => 'NO',
                'product_name' => $data['product_name'] ?? 'Payment',
                'product_category' => $data['product_category'] ?? 'General',
                'product_profile' => 'general',
            ];

            $response = Http::asForm()->post("{$this->baseUrl}/gwprocess/v4/api.php", $postData);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['status'] === 'SUCCESS') {
                    return [
                        'success' => true,
                        'payment_intent_id' => $postData['tran_id'],
                        'session_key' => $result['sessionkey'] ?? null,
                        'gateway_url' => $result['GatewayPageURL'] ?? null,
                        'redirect_url' => $result['GatewayPageURL'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'error' => $result['failedreason'] ?? 'Failed to initiate payment',
                ];
            }

            return ['success' => false, 'error' => 'Failed to connect to SSLCommerz'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function confirmPayment(string $paymentIntentId): array
    {
        return $this->validateTransaction($paymentIntentId);
    }

    public function validateTransaction(string $transactionId, string $amount = null): array
    {
        try {
            $postData = [
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'tran_id' => $transactionId,
            ];

            $response = Http::asForm()->post(
                "{$this->baseUrl}/validator/api/validationserverAPI.php",
                $postData
            );

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === 'VALID') {
                    if ($amount && $result['amount'] != $amount) {
                        return [
                            'success' => false,
                            'error' => 'Amount mismatch',
                        ];
                    }

                    return [
                        'success' => true,
                        'status' => 'completed',
                        'transaction_id' => $result['tran_id'],
                        'val_id' => $result['val_id'],
                        'amount' => $result['amount'],
                        'currency' => $result['currency'],
                        'card_type' => $result['card_type'] ?? null,
                        'bank_tran_id' => $result['bank_tran_id'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'status' => $result['status'] ?? 'INVALID',
                    'error' => 'Transaction validation failed',
                ];
            }

            return ['success' => false, 'error' => 'Failed to validate transaction'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            $postData = [
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'bank_tran_id' => $paymentId,
                'refund_amount' => $amount,
                'refund_remarks' => 'Customer refund request',
            ];

            $response = Http::asForm()->post(
                "{$this->baseUrl}/validator/api/merchantTransIDvalidationAPI.php",
                $postData
            );

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['status'] === 'success') {
                    return [
                        'success' => true,
                        'refund_id' => $result['refund_ref_id'] ?? null,
                        'status' => 'refunded',
                    ];
                }

                return [
                    'success' => false,
                    'error' => $result['errorReason'] ?? 'Refund failed',
                ];
            }

            return ['success' => false, 'error' => 'Failed to process refund'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        return $this->validateTransaction($paymentId);
    }

    public function createCustomer(array $customerData): array
    {
        return [
            'success' => true,
            'customer_id' => null,
            'message' => 'SSLCommerz does not require customer creation',
        ];
    }

    public function getGatewayName(): string
    {
        return Payment::GATEWAY_SSLCOMMERZ;
    }

    public function handleWebhook(array $payload): array
    {
        $status = $payload['status'] ?? null;
        $transactionId = $payload['tran_id'] ?? null;

        switch ($status) {
            case 'VALID':
                return [
                    'event' => 'payment_completed',
                    'transaction_id' => $transactionId,
                    'status' => 'completed',
                    'amount' => $payload['amount'] ?? 0,
                ];
            case 'FAILED':
                return [
                    'event' => 'payment_failed',
                    'transaction_id' => $transactionId,
                    'status' => 'failed',
                ];
            case 'CANCELLED':
                return [
                    'event' => 'payment_cancelled',
                    'transaction_id' => $transactionId,
                    'status' => 'cancelled',
                ];
            default:
                return ['event' => $status, 'handled' => false];
        }
    }

    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        return true;
    }
}
