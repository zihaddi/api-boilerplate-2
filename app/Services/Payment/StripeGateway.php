<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Customer;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Payment;
use Exception;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $webhookSecret;
    protected string $currency;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->secretKey = config('payment.gateways.stripe.secret', '');
        $this->webhookSecret = config('payment.gateways.stripe.webhook_secret', '');
        $this->currency = config('payment.gateways.stripe.currency', 'usd');
        
        if (!empty($this->secretKey)) {
            Stripe::setApiKey($this->secretKey);
            $this->isConfigured = true;
        }
    }

    protected function ensureConfigured(): void
    {
        if (!$this->isConfigured) {
            throw new \Exception('Stripe gateway is not configured. Please set STRIPE_SECRET_KEY environment variable.');
        }
    }

    public function createPaymentIntent(array $data): array
    {
        $this->ensureConfigured();
        try {
            $paymentIntentData = [
                'amount' => (int) ($data['amount'] * 100),
                'currency' => $data['currency'] ?? $this->currency,
                'payment_method_types' => ['card'],
                'metadata' => $data['metadata'] ?? [],
            ];

            if (!empty($data['customer_id'])) {
                $paymentIntentData['customer'] = $data['customer_id'];
            }

            if (!empty($data['description'])) {
                $paymentIntentData['description'] = $data['description'];
            }

            if (!empty($data['receipt_email'])) {
                $paymentIntentData['receipt_email'] = $data['receipt_email'];
            }

            $paymentIntent = PaymentIntent::create($paymentIntentData);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'requires_confirmation') {
                $paymentIntent = $paymentIntent->confirm();
            }

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'payment_method' => $paymentIntent->payment_method,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        try {
            $refundData = ['payment_intent' => $paymentId];
            
            if ($amount !== null) {
                $refundData['amount'] = (int) ($amount * 100);
            }

            $refund = Refund::create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'payment_method' => $paymentIntent->payment_method,
                'customer' => $paymentIntent->customer,
                'created' => $paymentIntent->created,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createCustomer(array $customerData): array
    {
        try {
            $customer = Customer::create([
                'email' => $customerData['email'] ?? null,
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'metadata' => $customerData['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'customer_id' => $customer->id,
                'email' => $customer->email,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getGatewayName(): string
    {
        return Payment::GATEWAY_STRIPE;
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? [];

        switch ($event) {
            case 'payment_intent.succeeded':
                return [
                    'event' => 'payment_completed',
                    'payment_intent_id' => $data['id'] ?? null,
                    'amount' => ($data['amount'] ?? 0) / 100,
                    'status' => 'completed',
                ];
            case 'payment_intent.payment_failed':
                return [
                    'event' => 'payment_failed',
                    'payment_intent_id' => $data['id'] ?? null,
                    'error' => $data['last_payment_error']['message'] ?? 'Unknown error',
                    'status' => 'failed',
                ];
            case 'charge.refunded':
                return [
                    'event' => 'payment_refunded',
                    'charge_id' => $data['id'] ?? null,
                    'amount_refunded' => ($data['amount_refunded'] ?? 0) / 100,
                    'status' => 'refunded',
                ];
            default:
                return ['event' => $event, 'handled' => false];
        }
    }

    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch (SignatureVerificationException $e) {
            return false;
        }
    }

    public function createCheckoutSession(array $data): array
    {
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $data['currency'] ?? $this->currency,
                        'product_data' => [
                            'name' => $data['product_name'] ?? 'Payment',
                            'description' => $data['description'] ?? null,
                        ],
                        'unit_amount' => (int) ($data['amount'] * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'metadata' => $data['metadata'] ?? [],
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
