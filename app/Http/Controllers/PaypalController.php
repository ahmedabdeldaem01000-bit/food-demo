<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaypalPaymentRequest;
use App\Services\Payment\PaypalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    public function __construct(
        protected PaypalService $paypalService
    ) {
    }

    public function createPayment(
        CreatePaypalPaymentRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $result = $this->paypalService->createPayment(
            (int) $request->user()->getKey(),
            [[
                'product_id' => (int) $data['product_id'],
                'quantity' => (int) $data['quantity'],
            ]],
            $data['discount_code'] ?? null,
            route('paypal.succes'),
            route('paypal.cance')
        );

        return response()->json([
            'status' => 'success',
            'approval_url' => $result['approval_url'],
            'paypal_order_id' => $result['paypal_order_id'],
            'order_id' => $result['order_id'],
            'amount' => $result['amount'],
            'discount_amount' => $result['discount_amount'],
            'currency' => 'USD',
        ]);
    }

    public function paymentSuccess(Request $request): JsonResponse
    {
        $token = $request->query('token');

        if (! is_string($token) || $token === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'PayPal token is required.',
            ], 422);
        }

        try {
            $capture = $this->paypalService->capturePayment(
                $token,
                $request->user() ? (int) $request->user()->getKey() : null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Payment completed',
                'data' => $capture,
            ]);
        } catch (\Throwable $exception) {
            Log::error('PayPal capture failed', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment could not be completed.',
            ], 400);
        }
    }

    public function paymentCancel(Request $request): JsonResponse
    {
        $token = $request->query('token');

        if (is_string($token) && $token !== '') {
            $this->paypalService->cancelPayment(
                $token,
                $request->user() ? (int) $request->user()->getKey() : null
            );
        }

        return response()->json(['status' => 'cancelled']);
    }

    public function webhook(Request $request): JsonResponse
    {
        if (! $this->paypalService->handleWebhook($request)) {
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        return response()->json(['status' => 'ok']);
    }
}
