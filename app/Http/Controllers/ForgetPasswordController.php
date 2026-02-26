<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
 public function forgotPassword(Request $request){
     $request->validate(['phone'=>'required']);
  
     $user=User::where('phone',$request->phone)->first();
 

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'No account found with this phone number'], 404);
    }
    $otp=1234;
    $expireAt=now()->addMinutes(5);
    Cache::put('otp'.$request['phone'],$otp,$expireAt);
    return response()->json(['message'=>'otp sended to'.$request->pone]);
    
 }   

public function verifyResetOtp(Request $request){
    $request->validate(['phone'=>'required','otp'=>'required']);

    $otpCache=Cache::get('otp'.$request['phone']);

 
    if(!$otpCache){
        return response()->json([
            'message'=>'nun number',
            'status'=>false,        
        ]);
    }
    if($otpCache != $request->otp){
        return response()->json([
            'message'=>'wrong otp',
            'status'=>false,        
        ],400);
    }
    return response()->json([
        'message'=>'otp verify successfully',
        'status'=>true
    ],200);
 
}

public function resetPassword(Request $request)
{
    $request->validate(['new_password'=>'required','phone'=>'required']);
    $user=User::where('phone',$request->phone)->first();

      if (!$user) {
        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    }

       $user->update([
        'password' => Hash::make($request->new_password),
        'otp_code' => null,
        'otp_expires_at' => null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Password reset successfully.'
    ]);
}


}
