<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request):JsonResponse
    {
        
$user=auth()->user();
if($user){

    $notifications = $user->notifications()->latest()->get();
}else{
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated.'
    ], 401);
}
        return response()->json([
            'status' => 'success',
            'data'   => $notifications
        ]);
    }
}
