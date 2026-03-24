<?php
namespace App\Services;

use App\Models\Category;
use App\Models\Product;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
 
use Exception;
class ProductService
{
   
    public function CategoryPage($data)
    {
   
                return [
                    'category_typ' => $this->CategoryById(1),
                ];
           

    }
    public function CategoryById($data)
    {
        if (!$data) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return Category::with(['products'])->findOrFail($data);

    }

    public function AllCategories(){
        return Category::get();

    }

    public function Rating(){
         return Product::orderByDesc('review')
            ->with(['category']) 
            ->take(10) 
            ->get();
    }

    public function destroy($data){
       try {
        $product = Product::findOrFail($data);

        // حذف الصورة من التخزين لو موجودة  ProductService
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


public function updateProduct($request, Product $product)
{
    DB::beginTransaction();

    try {

        $data = $request->validated();

        if ($request->hasFile('image')) {

            // نخزن الصورة الجديدة الأول
            $newImagePath = $request->file('image')
                                    ->store('products', 'public');

            // نحذف القديمة بعد ما الجديدة تتخزن
            if ($product->image && 
                Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $newImagePath;
        }

        $product->update($data);

        
 

        DB::commit();

        return $product->fresh();

    } catch (Exception $e) {

        DB::rollBack();

        // لو الصورة الجديدة اتخزنت وفشل التحديث نحذفها
        if (isset($newImagePath) && 
            Storage::disk('public')->exists($newImagePath)) {
            Storage::disk('public')->delete($newImagePath);
        }

        throw new Exception("Product update failed: " . $e->getMessage());
    }
}

public function createProduct($request)
{
    DB::beginTransaction();

    try {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                                     ->store('products', 'public');
        }

        $product = Product::create($data);

   

        DB::commit();

        return $product;

    } catch (Exception $e) {

        DB::rollBack();

        // لو الصورة اتخزنت وحصل error بعدها
        if (isset($data['image']) &&
            Storage::disk('public')->exists($data['image'])) {
            Storage::disk('public')->delete($data['image']);
        }

        throw new Exception("Product creation failed: " . $e->getMessage());
    }
}
    // 
 
}