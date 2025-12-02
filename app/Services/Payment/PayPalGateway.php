<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGatewayInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected string $currency;
    protected ?string $accessToken = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->clientId = config('payment.gateways.paypal.client_id', '');
        $this->clientSecret = config('payment.gateways.paypal.client_secret', '');
        $this->baseUrl = config('payment.gateways.paypal.api_url', 'https://api-m.sandbox.paypal.com');
        $this->currency = config('payment.gateways.paypal.currency', 'USD');
        $this->isConfigured = !empty($this->clientId) && !empty($this->clientSecret);
    }

    protected function ensureConfigured(): void
    {
        if (!$this->isConfigured) {
            throw new \Exception('PayPal gateway is not configured. Please set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables.');
        }
    }

    protected function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');
                return $this->accessToken;
            }

            return null;
        } catch (Exception $e) {
            Log::error('PayPal access token error: ' . $e->getMessage());
            return null;
        }
    }

    public function createPaymentIntent(array $data): array
    {
        $this->ensureConfigured();
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        try {
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $data['currency'] ?? $this->currency,
                        'value' => number_format($data['amount'], 2, '.', ''),
                    ],
                    'description' => $data['description'] ?? 'Payment',
                ]],
                'application_context' => [
                    'return_url' => $data['success_url'] ?? url('/api/payments/paypal/success'),
                    'cancel_url' => $data['cancel_url'] ?? url('/api/payments/paypal/cancel'),
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ];

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v2/checkout/orders", $orderData);

            if ($response->successful()) {
                $order = $response->json();
                $approvalUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

                return [
                    'success' => true,
                    'order_id' => $order['id'],
                    'status' => $order['status'],
                    'approval_url' => $approvalUrl,
                    'payment_intent_id' => $order['id'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to create PayPal order',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function confirmPayment(string $paymentIntentId): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$paymentIntentId}/capture");

            if ($response->successful()) {
                $capture = $response->json();
                $captureAmount = $capture['purchase_units'][0]['payments']['captures'][0] ?? null;

                return [
                    'success' => $capture['status'] === 'COMPLETED',
                    'order_id' => $capture['id'],
                    'status' => $capture['status'],
                    'amount' => $captureAmount['amount']['value'] ?? 0,
                    'currency' => $captureAmount['amount']['currency_code'] ?? $this->currency,
                    'capture_id' => $captureAmount['id'] ?? null,
                    'payment_intent_id' => $capture['id'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to capture payment',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refundPayment(string $paymentId, float $amount = null): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        try {
            $refundData = [];
            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency_code' => $this->currency,
                    'value' => number_format($amount, 2, '.', ''),
                ];
            }

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v2/payments/captures/{$paymentId}/refund", $refundData);

            if ($response->successful()) {
                $refund = $response->json();
                return [
                    'success' => true,
                    'refund_id' => $refund['id'],
                    'status' => $refund['status'],
                    'amount' => $refund['amount']['value'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to process refund',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPaymentDetails(string $paymentId): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/v2/checkout/orders/{$paymentId}");

            if ($response->successful()) {
                $order = $response->json();
                return [
                    'success' => true,
                    'order_id' => $order['id'],
                    'status' => $order['status'],
                    'amount' => $order['purchase_units'][0]['amount']['value'] ?? 0,
                    'currency' => $order['purchase_units'][0]['amount']['currency_code'] ?? $this->currency,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Failed to get order details',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createCustomer(array $customerData): array
    {
        return [
            'success' => true,
            'customer_id' => null,
            'message' => 'PayPal does not require customer creation',
        ];
    }

    public function getGatewayName(): string
    {
        return Payment::GATEWAY_PAYPAL;
    }

    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                return [
                    'event' => 'payment_approved',
                    'order_id' => $resource['id'] ?? null,
                    'status' => 'approved',
                ];
            case 'PAYMENT.CAPTURE.COMPLETED':
                return [
                    'event' => 'payment_completed',
                    'capture_id' => $resource['id'] ?? null,
                    'amount' => $resource['amount']['value'] ?? 0,
                    'status' => 'completed',
                ];
            case 'PAYMENT.CAPTURE.REFUNDED':
                return [
                    'event' => 'payment_refunded',
                    'refund_id' => $resource['id'] ?? null,
                    'amount' => $resource['amount']['value'] ?? 0,
                    'status' => 'refunded',
                ];
            default:
                return ['event' => $eventType, 'handled' => false];
        }
    }

    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        return true;
    }
}
