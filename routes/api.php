<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [ForgetPasswordController::class, 'verifyResetOtp']);
Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);



Route::get('/home', [ProductController::class, 'topRated']);
Route::get('/category-page', [ProductController::class, 'CategoryPage']);
Route::get('/categories/{id}/products', [ProductController::class, 'productsByCategory']);


// User routes
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':2,1'])->group(function () {


    Route::post('/paypal/create-payment', [PaypalController::class, 'createPayment']);
    Route::get('/paypal/success', [PaypalController::class, 'paymentSuccess'])->name('paypal.success');
    Route::get('/paypal/cancel', [PaypalController::class, 'paymentCancel'])->name('paypal.cancel');
    Route::post('/paypal/webhook', [PaypalController::class, 'webhook']);
    Route::post('/user/fcm-token', [UserController::class, 'storeFcmToken']);


    Route::get('/profile/{id}', [UserProfileController::class, 'profilePage']);
    Route::get('/profile/{id}', [UserProfileController::class, 'personal']);
    Route::get('/profile-order/{id}', [UserProfileController::class, 'userOrder']);
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');



    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{productId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);


});




// Admin routes
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':1'])->group(function () {
    Route::post('/add-products', [ProductController::class, 'store']);
    Route::post('/update-product/{product}', [ProductController::class, 'update']);
    Route::delete('/delete-product/{id}', [ProductController::class, 'destroy']);
    Route::post('/add-category', [CategoryController::class, 'store']);
    Route::delete('/delete-category/{id}', [CategoryController::class, 'destroy']);
    Route::post('/update-category/{category}', [CategoryController::class, 'update']);

        Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);

});




// Route::post('/test-notification', function (Request $request) {
//     $request->validate([
//         'fcm_token' => 'required|string',
//         'title' => 'required|string',
//         'body' => 'required|string',
//     ]);

//     $messaging = Firebase::messaging();

//     $notification = Notification::create($request->title, $request->body);
//     $message = CloudMessage::withTarget('token', $request->fcm_token)
//         ->withNotification($notification);

//     try {
//         $messaging->send($message);
//         return response()->json(['status' => 'ok', 'message' => 'Notification sent']);
//     } catch (\Throwable $e) {
//         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
//     }
// });