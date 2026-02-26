<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['phone'=>'required']);
        $otp = 1234;
        Cache::put('otp_' . $request->phone, $otp, now()->addMinutes(5));


        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $twilio->messages->create(
                'WhatsApp:' . $request->phone,
                [
                    'from' => env('TWILIO_WHATSAPP_FROM'),

                    'body' => 'otp number is ' . $otp
                ]
            );
        } catch (RestException $e) {
            return response()->json([
                'message' => 'please check phone number',
                'status' => false,
                'error' => $e->getMessage(),
                'phone' => $request->phone
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'OTP sent to WhatsApp.',
            'phone' => $request->phone
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);

        $cachedOtp = Cache::get('otp_' . $request['phone']);

        if (!$cachedOtp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired or not found.'
            ], 400);
        }

        if ($cachedOtp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Wrong OTP.'
            ], 400);
        }

        // لو صح، خزّن حالة التحقق مع رقم الهاتف
        Cache::put('otp_verified_' . $request->phone, true, now()->addMinutes(5));

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Please enter your details.',
            'phone' => $request->phone
        ]);
    }





    public function register(AuthRequest $request)
    {
        $data = $request->validated();
 
        $otpVerified = Cache::get('otp_verified_' . $data['phone']);
        if (!$otpVerified) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not verified.'
            ], 400);
        }
 

        $user = User::create([
            'phone' => $data['phone'],       // مهم جدًا
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'address' => $data['address'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'photo' => $data['photo'] ?? asset('hero.jpg')
        ]);
$user->assignRole('user');

        // مسح OTP بعد التسجيل
        Cache::forget('otp_' . $data['phone']);
        Cache::forget('otp_verified_' . $data['phone']);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'user' => $user
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
