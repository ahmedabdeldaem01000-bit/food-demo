<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    public function index():JsonResponse
    {
        $product = Product::query()->latest()->paginate(10);
        return response()->json([
            'data' => $product
        ]);

    }

    public function store(ProductRequest $request):JsonResponse
    {
        try {

            $product = $this->productService->createProduct($request);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المنتج بنجاح ✅',
                'data' => $product,
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المنتج',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, Product $product):JsonResponse
    {
        try {

            $updated = $this->productService->updateProduct($request, $product);

            return response()->json([
                'success' => true,
                'data' => $updated
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  public function destroy(Product $product): JsonResponse
{
    $this->productService->destroy($product);

    return response()->json([
        'success' => true,
        'message' => 'تم حذف المنتج'
    ]);
}

    public function topRated():JsonResponse
    {


        $topProducts = $this->productService->Rating();
        return response()->json([
            'status' => 'success',
            'data' => $topProducts,
        ]);
    }
    public function CategoryPage():JsonResponse
    {
        $category = $this->productService->AllCategories();
        return response()->json($category);
    }

    // 🟢 Get products by category
    public function productsByCategory(string $id):JsonResponse
    {

        $category = $this->productService->CategoryById($id);


        return response()->json([
            'category' => $category,
            'products' => $category->products,
        ]);
    }
}
