<?php

namespace App\Http\Controllers\web\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\HomeService;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
            
    }
    
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $data = $this->homeService->homePage();
        return view('web.home.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
 
public function show(Product $product)
{
    $product->load('category');
    return view('web.product.index', ['data' => $product]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function home()
    {
        return view('web.home.index.index');
    }
    public function category()
    {
        return view('web.home.index.category');
    }
 

    

    // public function add(Request $request)
    // {
    //     $request->validate([
    //         'product_id' => 'required|exists:products,id'
    //     ]);

    //     $this->cartService->add(
    //         auth()->id(),
    //         $request->product_id
    //     );

    //     return back()->with('success','Product added to cart');
    // }
      
}
