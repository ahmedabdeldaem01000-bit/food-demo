<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaypalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Js;

class PaypalController extends Controller
{
    protected PaypalService $paypalService;

    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }


    public function createPayment(Request $request): JsonResponse
    {

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'product_id' => 'required|exists:products,id',
        ]);


        $user = Auth::user();
if($user){
    
        $result = $this->paypalService->createPayment(
            $user['id'],
            $data['amount'],
            $data['product_id']
        );

        return response()->json([
            'status' => 'success',
            'approval_url' => $result['approval_url'],
            'order_id' => $result['order_id']
        ]);
}else{
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated.'
    ], 401);
}
    }

    public function paymentSuccess(Request $request):JsonResponse
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

    public function paymentCancel():JsonResponse
    {
        return response()->json([
            'status' => 'cancelled'
        ]);
    }

    public function webhook(Request $request):JsonResponse
    {
        $this->paypalService->handleWebhook($request->all());

        return response()->json(['status' => 'ok']);
    }
}