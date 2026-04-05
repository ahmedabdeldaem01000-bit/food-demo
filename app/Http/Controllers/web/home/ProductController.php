<?php

namespace App\Http\Controllers\web\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\HomeService;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected HomeService $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $data = $this->homeService->homePage();
        return view('web.home.index', compact('data'));
    }



    /**
     * Display the specified resource.
     */

    public function show(Product $product): View
    {
        $product->load('category');
        return view('web.product.index', ['data' => $product]);
    }



    public function home(): View
    {
        return view('web.home.index');
    }
    public function category(): View
    {
        return view('web.category.category');
    }





}
