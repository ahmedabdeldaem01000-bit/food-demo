<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function profilePage($id){
         
      $data = User::select('name', 'photo')->findOrFail($id);



        return response()->json(['data'=>$data]);
    }
    public function personal($id){
         
      $data = User::findOrFail($id);
        return response()->json(['data'=>$data]);
    }
    public function personalEdit(Request $request,$id){
      $data = $request->validate([
        'name'       => 'required|string',
        'email'      => 'required|email',
        'phone'      => 'required|string',
        'birth_date' => 'required|date',
        'address'    => 'required|string',
    ]);

    $user = User::findOrFail($id);
    $user->update($data);

    return response()->json([
        'status'  => 'success',
        'message' => 'User updated successfully',
        'data'    => $user->fresh(['id', 'name', 'email', 'phone', 'birth_date', 'address']),
    ], 200);
    }


    public function userOrder($request){
         $user = Auth::user();

        // كل الطلبات المرتبطة بيه
        $orders = $user->order()
            ->with(['product']) // ممكن تضيف علاقات زي products لو حابب
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
}
