<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class AuthController extends Controller
{
        public function __construct(private AuthService $authService) {}
 

    public function verifyOtp(VerifyOtpRequest $request)
    {
         $user = $this->authService->verifyOtp($request->validated());

        auth()->login($user); 
        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Please enter your details.',
            'email' => $user->email
        ]);
    }





    public function register(RegisterRequest $request)
    {
               $this->authService->sendOtp($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
             
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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
            'user' => $user
        ]);
    }


}
