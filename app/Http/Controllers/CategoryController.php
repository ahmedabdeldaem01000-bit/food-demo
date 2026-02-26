<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json(['success' => true, 'data' => $categories], 200);
    }

    // Create category
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء التصنيف بنجاح ✅',
            'data' => $category,
        ], 201);
    }

    // Update category
    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        } else {
            $data['image'] = $category->image;
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التصنيف بنجاح ✅',
            'data' => $category
        ]);
    }


    // Delete category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // حذف الصورة لو موجودة
        if ($category->image && \Storage::disk('public')->exists($category->image)) {
            \Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التصنيف بنجاح ✅',
        ], 200);
    }
}
