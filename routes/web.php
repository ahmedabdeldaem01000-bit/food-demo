<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\web\NotificationController;
use App\Http\Controllers\web\payment\PaypalController;
use App\Http\Controllers\web\profile\UserController;
use App\Http\Controllers\web\Auth\AuthController;
use App\Http\Controllers\web\cart\CartController;
use App\Http\Controllers\web\category\CategoryController;
use App\Http\Controllers\web\Favorite\FavoriteController;
use App\Http\Controllers\web\Home\ProductController;
use App\Http\Controllers\web\DiscountController;
use App\Models\User;
use App\Notifications\ProductNotification;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {

    // ================= Public =================
    Route::get('home', 'home')->name('home.index');

    // ================= Auth Actions =================
    Route::post('login', 'login')->name('login.submit');
    Route::post('logout', 'logout')->middleware('auth')->name('logout');
    Route::post('register', 'register')->name('register.submit');

    // ================= Guest Only =================
    Route::middleware('guest')->group(function () {

        Route::get('register', 'showRegister')->name('register.show');

        Route::get('show-otp', 'showOtp')->name('otp.showOtp');
        Route::post('verify-otp', 'verify')->name('otp.verify');


        Route::get('forget-password', 'showForgetPassword')->name('forget-password.show');
        Route::get('otp-reset-password', 'otpResetPassword')->name('otp-reset-password.show');
        Route::post('otp-forget-password', 'otpForgetPassword')->name('otp-forget-password.submit');



        Route::get('new-password', 'newPassword')->name('new-password.show');
        Route::post('update-new-password', 'updateNewPassword')->name('update-new-password.submit');

        Route::post('update-password', 'updatePassword')->name('update-password.submit');

    });

    // ================= Password Reset (Auth + OTP Verified) =================

    // Route::middleware('auth')->group(function () {
    //     Route::get('forget-password', 'showForgetPassword')->name('forget-password.show');
    //     Route::post('otp-forget-password', 'otpForgetPassword')->name('otp-forget-password.submit');

    // });

});

Route::get('login', [AuthController::class, 'showLogin'])->name('login.show');

Route::get('home', [ProductController::class, 'index'])->name('home.index');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::post('/paypal/webhook', [\App\Http\Controllers\web\payment\PaypalController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);


Route::middleware('auth')->group(function () {
     Route::get('show-favorite', [FavoriteController::class, 'showFavorite'])->name('favorite.showFavorite');
    Route::get('profile', [UserController::class, 'profilePage'])->name('profile.profilePage');
    Route::get('profile-setting', [UserController::class, 'personalEditShow'])->name('profile.personalEditShow');
    Route::put('profile-update', [UserController::class, 'personalEdit'])->name('profile.personalEdit');
    Route::get('profile-orders', [UserController::class, 'showOrders'])->name('profile.showOrders');
    Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle')->middleware('auth');
 
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart-decrease/{product}', [CartController::class, 'decrease'])->name('cart.decrease');
    Route::post('/cart-increase/{product}', [CartController::class, 'increase'])->name('cart.increase');
    Route::delete('/cart-remove/{product}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('get-cart', [CartController::class, 'index'])->name('get-cart.index');
    Route::post('/discount/apply', [DiscountController::class, 'apply'])->name('discount.apply');
    Route::delete('/discount/remove', [DiscountController::class, 'remove'])->name('discount.remove');
    Route::post('/paypal/create-payment', [PaypalController::class, 'createPayment'])->name('paypal.createPayment');
    Route::get('/paypal/success', [PaypalController::class, 'paymentSuccess'])->name('paypal.success');
    Route::get('/payment/already_done', [PaypalController::class, 'alreadyDone'])->name('paypal.alreadyDone');
    Route::get('/paypal/cancel', [PaypalController::class, 'paymentCancel'])->name('paypal.cancel');
    Route::post('/user/fcm-token', [UserController::class, 'storeFcmToken']);
    Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');
    Route::get('/tracking', [LocationController::class, 'index'])->name('location.index');
    Route::post('/tracking/update', [LocationController::class, 'update'])->name('location.update');



});



Route::get('category', [CategoryController::class, 'index'])->name('category.index');
Route::get('category/{category}', [CategoryController::class, 'show'])->name('category.show');
