<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    public function createPaymentIntent(array $data): array;
    
    public function confirmPayment(string $paymentIntentId): array;
    
    public function refundPayment(string $paymentId, float $amount = null): array;
    
    public function getPaymentDetails(string $paymentId): array;
    
    public function createCustomer(array $customerData): array;
    
    public function getGatewayName(): string;
    
    public function handleWebhook(array $payload): array;
    
    public function validateWebhookSignature(string $payload, string $signature): bool;
}
