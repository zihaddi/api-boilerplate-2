<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponses;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    use HttpResponses;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('check.permission:view')->only(['index', 'show', 'stats']);
        $this->middleware('check.permission:add')->only(['initiate', 'confirm']);
        $this->middleware('check.permission:edit')->only(['refund']);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'gateway', 'from_date', 'to_date', 'search', 'per_page']);
            
            $payments = Payment::filter($filters)
                ->with(['user:id,email', 'createdBy:id,email'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return $this->success($payments, 'Payments retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show(string $transactionId): JsonResponse
    {
        try {
            $result = $this->paymentService->getPaymentDetails($transactionId);

            if ($result['success']) {
                return $this->success($result, 'Payment details retrieved', Response::HTTP_OK, true);
            }

            return $this->error(null, $result['error'], Response::HTTP_NOT_FOUND, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gateway' => 'required|string|in:stripe,paypal,sslcommerz',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, false);
        }

        try {
            $result = $this->paymentService->initiatePayment($request->all());

            if ($result['success']) {
                return $this->success($result, 'Payment initiated successfully', Response::HTTP_CREATED, true);
            }

            return $this->error(null, $result['error'], Response::HTTP_BAD_REQUEST, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function confirm(Request $request, string $transactionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gateway_payment_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, false);
        }

        try {
            $result = $this->paymentService->confirmPayment(
                $transactionId,
                $request->input('gateway_payment_id')
            );

            if ($result['success']) {
                return $this->success($result, 'Payment confirmed successfully', Response::HTTP_OK, true);
            }

            return $this->error(null, $result['error'], Response::HTTP_BAD_REQUEST, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function refund(Request $request, string $transactionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, false);
        }

        try {
            $result = $this->paymentService->refundPayment(
                $transactionId,
                $request->input('amount'),
                $request->input('reason')
            );

            if ($result['success']) {
                return $this->success($result, 'Payment refunded successfully', Response::HTTP_OK, true);
            }

            return $this->error(null, $result['error'], Response::HTTP_BAD_REQUEST, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['from_date', 'to_date', 'gateway', 'status']);
            $stats = $this->paymentService->getPaymentStats($filters);

            return $this->success($stats, 'Payment statistics retrieved', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function gateways(): JsonResponse
    {
        try {
            $gateways = $this->paymentService->getAvailableGateways();

            return $this->success([
                'gateways' => $gateways,
                'default' => Payment::GATEWAY_STRIPE,
            ], 'Available gateways retrieved', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $signature = $request->header('Stripe-Signature');

            $result = $this->paymentService->handleWebhook(Payment::GATEWAY_STRIPE, $payload, $signature);

            return response()->json(['received' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function paypalWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $result = $this->paymentService->handleWebhook(Payment::GATEWAY_PAYPAL, $payload);

            return response()->json(['received' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sslcommerzSuccess(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $result = $this->paymentService->handleWebhook(Payment::GATEWAY_SSLCOMMERZ, $payload);

            if ($result['status'] === 'completed') {
                $transactionId = $payload['tran_id'] ?? null;
                if ($transactionId) {
                    $this->paymentService->confirmPayment($transactionId);
                }
            }

            return $this->success($result, 'Payment processed', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function sslcommerzFail(Request $request): JsonResponse
    {
        try {
            $transactionId = $request->input('tran_id');
            
            $payment = Payment::where('transaction_id', $transactionId)
                ->orWhere('gateway_transaction_id', $transactionId)
                ->first();

            if ($payment) {
                $payment->markAsFailed('Payment failed at gateway');
            }

            return $this->error(null, 'Payment failed', Response::HTTP_OK, false);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function sslcommerzCancel(Request $request): JsonResponse
    {
        try {
            $transactionId = $request->input('tran_id');
            
            $payment = Payment::where('transaction_id', $transactionId)
                ->orWhere('gateway_transaction_id', $transactionId)
                ->first();

            if ($payment) {
                $payment->update(['status' => Payment::STATUS_CANCELLED]);
            }

            return $this->success(null, 'Payment cancelled', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function sslcommerzIpn(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $result = $this->paymentService->handleWebhook(Payment::GATEWAY_SSLCOMMERZ, $payload);

            return response()->json(['received' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function userPayments(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $filters = $request->only(['status', 'gateway', 'from_date', 'to_date', 'per_page']);
            
            $payments = $this->paymentService->getPaymentsByUser($userId, $filters);

            return $this->success($payments, 'User payments retrieved', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
