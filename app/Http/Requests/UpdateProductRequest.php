<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'image' => 'sometimes',
            'description' => 'sometimes|string',
            'ingredients' => 'sometimes|array',
            'ingredients.*.name' => 'sometimes|string',
            'kcal' => 'sometimes|string',
            'review' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'category_id' => 'sometimes|exists:categories,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب.',
            'image.required' => 'يجب رفع صورة المنتج.',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'description.required' => 'الوصف مطلوب.',
            'ingredients.required' => 'المكونات مطلوبة.',
            'kcal.required' => 'عدد السعرات مطلوب.',
            'price.required' => 'السعر مطلوب.',
            'price.numeric' => 'السعر يجب أن يكون رقمًا.',
            'category_id.required' => 'اختر التصنيف.',
            'category_id.exists' => 'التصنيف المختار غير موجود.',
        ];
    }
}
