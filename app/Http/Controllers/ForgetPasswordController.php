<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }
    public function forgotPassword(Request $request):JsonResponse
    {
        $this->authService->sendOtpPassword(request()->validate([
            'email' => 'required|email',
        ]));



        return response()->json(['message' => 'otp sended to ' . $request['email']]);

    }

    public function verifyResetOtp(Request $request):JsonResponse
    {
        $this->authService->verifyOtpPassword($request->validate([
            'code' => 'required',
        ]));


        return response()->json([
            'message' => 'otp verify successfully',
            'status' => true
        ], 200);

    }

 

public function resetPassword(Request $request): JsonResponse
{
    $request->validate([
        'password' => 'required|confirmed|min:6',
    ]);

    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.'
        ], 401);
    }

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Password reset successfully.'
    ]);
}


}
