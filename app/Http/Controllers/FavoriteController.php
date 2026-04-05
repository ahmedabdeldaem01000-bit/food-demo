<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{

 
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

 
    public function index():JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getFavorites()
        ], 200);
    }

   public function toggle(int $productId): JsonResponse
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $status = $this->userService->toggleFavorite($user, $productId);

    return response()->json([
        'status' => $status,
        'is_favorite' => $status === 'added'
    ]);
}
 
}