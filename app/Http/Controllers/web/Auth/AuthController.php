<?php

namespace App\Http\Controllers\web\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendSmsNotification;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }


    public function register(RegisterRequest $request)
    {
        // dd('dddd');
        $this->authService->sendOtp($request->validated());

        return redirect()->route('otp.showOtp')
            ->with('email', $request->email);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // حماية من session fixation

            // مثال على استخدام Spatie Permission للتوجيه
            if (Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('home.show');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة، يرجى التأكد من الإيميل وكلمة المرور.',
        ])->withInput($request->only('email')); // للحفاظ على الإيميل المكتوب
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

 return redirect()->route('login.show')
    ->with('success', 'Logged out successfully');
    }

    public function showLogin()
    {
        return view('web.auth.login');
    }


    public function showRegister(Request $request)
    {
        return view('web.auth.register');
    }
    public function showOtp(Request $request)
    {
        return view('web.auth.otp');
    }
    public function showHome()
    {
        return view('web.home.index');
    }




    public function verify(VerifyOtpRequest $request)
    {

        $user = $this->authService->verifyOtp($request->validated());

        auth()->login($user);

        return redirect()->route('home.show');
    }
    // -----------------------------------------------------------
// -----------------------------------------------------------

    public function showForgetPassword()
    {
        return view('web.auth.forget-password');
    }


    public function otpForgetPassword()
    {
        $this->authService->sendOtpPassword(request()->validate([
            'email' => 'required|email',
        ]));
        return view('web.auth.otp-reset-password');

    }

    public function otpResetPassword()
    {
 
        return view('web.auth.otp-reset-password');
    }

    public function newPassword()
    {
    
        return view('web.auth.new-password');
    }
    public function updatePassword(VerifyOtpRequest $request)
    {

         $this->authService->verifyOtpPassword($request->validated());

      

       return view('web.auth.new-password');
    }


    public function updateNewPassword(Request $request)
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

        return redirect()->route('login.show')
            ->with('success', 'Password updated successfully.');
    }

}

