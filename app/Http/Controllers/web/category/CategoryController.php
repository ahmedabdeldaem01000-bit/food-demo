<?php

namespace App\Http\Controllers\web\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories=Category::get();
        return view('web.category.category',compact('categories'));
    }

   
 

  

    /**
     * Display the specified resource.
     */
   public function show(Category $category): View
{
    $products = $category['products'];
        
    return view('web.shop.index', compact('category', 'products'));
}

 

 
}
