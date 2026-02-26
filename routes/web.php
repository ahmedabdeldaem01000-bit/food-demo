<?php

// use App\Http\Controllers\web\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use pp\Http\Controllers\web\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(AuthController::class)->group(function () {

    Route::get('login', 'showLogin')->name('login');
    Route::post('login', 'login')->name('login.submit');

    Route::get('register', 'showRegister')->name('register');
    Route::post('register', 'register')->name('register.submit');

    Route::post('send-otp', 'sendOtp')->name('otp.send');

    Route::get('otp', 'showOtp')->name('otp.form');
    Route::post('verify-otp', 'verifyOtp')->name('otp.verify');

    Route::post('logout', 'logout')->name('logout');
});

// Route::get('/', fn() => view('home'))->middleware('auth')->name('home');