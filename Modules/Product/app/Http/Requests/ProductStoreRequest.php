<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|lt:price',
            'availability_status' => 'required|in:coming_soon,available,unavailable',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'specialties' => 'array',
            'specialties.*' => 'exists:specialties,id',
            'initial_stock' => 'nullable|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان محصول',
            'price' => 'قیمت',
            'discount' => 'تخفیف',
            'availability_status' => 'وضعیت موجودی',
            'status' => 'وضعیت فعال',
            'description' => 'توضیحات',
            'categories' => 'دسته‌بندی‌ها',
            'specialties' => 'تخصص‌ها',
            'initial_stock' => 'موجودی اولیه',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'وارد کردن عنوان محصول الزامی است.',
            'title.max' => 'عنوان محصول نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'price.required' => 'وارد کردن قیمت الزامی است.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'discount.lt' => 'تخفیف نمی‌تواند بیشتر یا مساوی قیمت باشد.',
            'categories.*.exists' => 'دسته‌بندی انتخاب شده معتبر نیست.',
            'specialties.*.exists' => 'تخصص انتخاب شده معتبر نیست.',
        ];
    }
}
