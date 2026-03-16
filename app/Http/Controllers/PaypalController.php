<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    protected $paypalService;

    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }


    public function createPayment(Request $request)
    {

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'product_id' => 'required|exists:products,id',
        ]);

        $result = $this->paypalService->createPayment(
            auth()->id(),
            $data['amount'],
            $data['product_id']
        );

        return response()->json([
            'status' => 'success',
            'approval_url' => $result['approval_url'],
            'order_id' => $result['order_id']
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        try {

            $capture = $this->paypalService->capturePayment(
                $request->query('token')
            );
            

            return response()->json([
                'status' => 'success',
                'message' => 'Payment completed',
                'data' => $capture
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function paymentCancel()
    {
        return response()->json([
            'status' => 'cancelled'
        ]);
    }

    public function webhook(Request $request)
    {
        $this->paypalService->handleWebhook($request->all());

        return response()->json(['status' => 'ok']);
    }
}