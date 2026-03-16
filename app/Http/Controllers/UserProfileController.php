<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profilePage()
    {
        return response()->json([
            'data' => $this->userService->getProfile()
        ]);
    }

    public function personalEdit(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string',
            'email'      => 'required|email',
            'phone'      => 'required|string',
            'birth_date' => 'required|date',
            'address'    => 'required|string',
        ]);

        $user = $this->userService->updateProfile($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully',
            'data'    => $user
        ]);
    }

    public function userOrder()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getUserOrders()
        ]);
    }
}