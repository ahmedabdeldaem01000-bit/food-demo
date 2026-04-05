<?php

namespace App\Http\Controllers\web\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendSmsNotification;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;


class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }


    public function register(RegisterRequest $request): RedirectResponse
    {

        $this->authService->sendOtp($request->validated());

        return redirect()->route('otp.showOtp')
            ->with('email', $request['email']);
    }
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // حماية من session fixation

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('home.index');
        }

        return back()->withErrors([
            'email' => 'بيانات الدخول غير صحيحة، يرجى التأكد من الإيميل وكلمة المرور.',
        ])->withInput($request->only('email')); // للحفاظ على الإيميل المكتوب
    }
    public function logout(Request $request):RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show')
            ->with('success', 'Logged out successfully');
    }

    public function showLogin():View
    {
        return view('web.auth.login');
    }


    public function showRegister(Request $request):View
    {
        return view('web.auth.register');
    }
    public function showOtp(Request $request):View
    {
        return view('web.auth.otp');
    }
    public function showHome():View
    {
        return view('web.home.index');
    }




    public function verify(VerifyOtpRequest $request):RedirectResponse
    {

        $user = $this->authService->verifyOtp($request->validated());

        Auth::login($user);


        return redirect()->route('home.index');
    }
    // -----------------------------------------------------------
// -----------------------------------------------------------

    public function showForgetPassword():View
    {
        return view('web.auth.forget-password');
    }


    public function otpForgetPassword():View
    {
        $this->authService->sendOtpPassword(request()->validate([
            'email' => 'required|email',
        ]));
        return view('web.auth.otp-reset-password');

    }


    public function otpResetPassword():View
    {

        return view('web.auth.otp-reset-password');
    }

    public function newPassword():View
    {

        return view('web.auth.new-password');
    }

/**
 * Summary of updatePassword
 * @param VerifyOtpRequest $request
 * @return View
 */
    public function updatePassword(VerifyOtpRequest $request):View
    {

        $user = $this->authService->verifyOtpPassword($request->validated());
        /** @var \App\Models\User|null $user */
if (!$user) {
    abort(404); // أو redirect
}

return view('web.auth.new-password', [
    'userId' => $user->id
]);
    }



    public function updateNewPassword(Request $request):RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|confirmed|min:6',
        ]);

        // $user = User::query()->findOrFail($request->input('user_id'));
   /** @var User $user */
    $user = User::findOrFail((int) $request->input('user_id'));
        $user->update([
            'password' => bcrypt($request->input('password')),
        ]);

        return redirect()->route('login.show')
            ->with('success', 'Password updated successfully.');
    }

}

