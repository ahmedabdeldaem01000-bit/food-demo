<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function storeFcmToken(Request $request):JsonResponse
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    $user = $request->user();
  if($user){
  $user->update(['fcm_token' => $request['fcm_token']]);

    return response()->json(['message' => 'FCM token saved']);
  }else{
return response()->json(['message' => 'User not authenticated'], 401);
  }
}
 
}
