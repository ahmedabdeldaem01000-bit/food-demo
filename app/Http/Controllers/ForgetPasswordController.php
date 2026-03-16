<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }
    public function forgotPassword(Request $request)
    {
        $this->authService->sendOtpPassword(request()->validate([
            'email' => 'required|email',
        ]));



        return response()->json(['message' => 'otp sended to ' . $request->email]);

    }

    public function verifyResetOtp(Request $request)
    {
        $this->authService->verifyOtpPassword($request->validate([
            'code' => 'required',
        ]));


        return response()->json([
            'message' => 'otp verify successfully',
            'status' => true
        ], 200);

    }

    public function resetPassword(Request $request)
    {
        $request->validate([

            'password' => 'required|confirmed|min:6',
        ]);

        $data = auth()->user();
     

        $user = User::where('email', $data->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'User not found'
            ]);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.'
        ]);
    }


}
