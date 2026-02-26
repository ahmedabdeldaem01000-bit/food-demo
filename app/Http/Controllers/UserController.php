<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function storeFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    $user = $request->user();
    $user->update(['fcm_token' => $request->fcm_token]);

    return response()->json(['message' => 'FCM token saved']);
}
}
