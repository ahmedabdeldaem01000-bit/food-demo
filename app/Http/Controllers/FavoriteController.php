<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class FavoriteController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getFavorites()
        ]);
    }

    public function store($productId)
    {
        $this->userService->addFavorite($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to favorites.'
        ]);
    }

    public function destroy($productId)
    {
        $this->userService->removeFavorite($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from favorites.'
        ]);
    }
}