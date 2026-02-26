<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
 
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
class ProductController extends Controller
{
    public function index()
    {
        $product = Product::get();
        return response()->json([
            'data' => $product
        ]);

    }

public function store(ProductRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('products', 'public');
    }

    $product = Product::create($data);

    return response()->json([
        'success' => true,
        'message' => 'تم إضافة المنتج بنجاح ✅',
        'data' => $product,
    ], 201);
}

  public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if($request->hasFile('image')){
            if($product->image){
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products','public');
        } else {
            $data['image'] = $product->image;
        }

        $product->update($data);

        return response()->json(['success'=>true,'message'=>'تم تحديث المنتج','data'=>$product]);
    }

public function destroy($id)
{
    try {
        $product = Product::findOrFail($id);

        // حذف الصورة من التخزين لو موجودة
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        // حذف المنتج نهائياً
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح ✅',
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'المنتج غير موجود.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء حذف المنتج.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function topRated()
    {
        // بنرتب المنتجات حسب التقييم من الأعلى للأقل
        $topProducts = Product::orderByDesc('review')
            ->with(['category']) // لو عندك العلاقات دي
            ->take(10) // عدد المنتجات اللي عايز ترجعها
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $topProducts,
        ]);
    }
    public function CategoryPage()
    {
        $categories = Category::select('id', 'name','image')->get();
        return response()->json($categories);
    }

    // 🟢 Get products by category
    public function productsByCategory($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'category' => $category->only(['id', 'name']),
            'products' => $category->products,
        ]);
    }
}
