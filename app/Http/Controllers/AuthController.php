<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Otp;
use App\Models\User;
use App\Services\AuthService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }


    public function logout(Request $request): JsonResponse
    {

        $user = Auth::user();
        if ($user) {
            $user->currentAccessToken()->delete();

        } else {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = $this->authService->verifyOtp($request->validated());


        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'success' => true,
            'message' => 'OTP verified. User registered successfully.',
            'user' => $user,
            'token' => $token // الموبايل هيحفظ التوكن ده ويبعته مع كل طلب بعدين
        ]);
    }





    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->authService->sendOtp($data);
        $otpRecord = Otp::query()->where('email', $data['email'])->first();
        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.'
            ], 500);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Please check your email for the OTP code.',
                'code' => $otpRecord['code']

            ]);
        }


    }


    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::query()->where('email', $request->input('email'))->first();


        if (!$user || !Hash::check($request->input('password'), $user['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'role' => $user->getRoleNames()->first(),
        ]);
    }


}
