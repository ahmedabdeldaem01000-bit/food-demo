<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\Auth\AuthController;

Route::controller(AuthController::class)->group(function () {

    // ================= Public =================
    Route::get('home', 'showHome')->name('home.show');

    // ================= Auth Actions =================
    Route::post('login', 'login')->name('login.submit');
    Route::post('logout', 'logout')->middleware('auth')->name('logout');

    Route::post('register', 'register')->name('register.submit');

    // ================= Guest Only =================
    Route::middleware('guest')->group(function () {

        Route::get('login', 'showLogin')->name('login.show');
        Route::get('register', 'showRegister')->name('register.show');

        Route::get('show-otp', 'showOtp')->name('otp.showOtp');
        Route::post('verify-otp', 'verify')->name('otp.verify');


    });

    // ================= Password Reset (Auth + OTP Verified) =================
    Route::middleware(['auth','check.otp'])->group(function () {
                Route::get('forget-password', 'showForgetPassword')->name('forget-password.show');
        Route::post('otp-forget-password', 'otpForgetPassword')->name('otp-forget-password.submit');


        Route::get('otp-reset-password', 'otpResetPassword')->name('otp-reset-password.show');

        Route::get('new-password', 'newPassword')->name('new-password.show');
        Route::post('update-new-password', 'updateNewPassword')->name('update-new-password.submit');

        Route::post('update-password', 'updatePassword')->name('update-password.submit');
    });
    
        Route::middleware('auth')->group(function () {
                 Route::get('forget-password', 'showForgetPassword')->name('forget-password.show');
        Route::post('otp-forget-password', 'otpForgetPassword')->name('otp-forget-password.submit');
    });

});