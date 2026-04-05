<?php
namespace App\Services;

use App\Models\Category;
use App\Models\Product;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
 
use Exception;
class ProductService
{
   
    public function CategoryPage(mixed $data):mixed
    {
   
                return [
                    'category_typ' => $this->CategoryById(1),
                ];
           

    }
    public function CategoryById(mixed $data):mixed
    {
        if (!$data) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return Category::with(['products'])->findOrFail($data);

    }
 
 

    /** @return \Illuminate\Database\Eloquent\Collection<int, Category> */
public function AllCategories(): Collection
{
    return Category::get();
}
    /**
     * Summary of Rating
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function Rating():Collection
    {
         return Product::orderByDesc('review')
            ->with(['category']) 
            ->take(10) 
            ->get();
    }

  
public function destroy(Product $product): void
{
    if ($product['image'] && Storage::disk('public')->exists($product['image'])) {
        Storage::disk('public')->delete($product['image']);
    }

    $product->delete();
}


public function updateProduct(mixed $request , Product $product):mixed
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

public function createProduct(mixed $request):mixed
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