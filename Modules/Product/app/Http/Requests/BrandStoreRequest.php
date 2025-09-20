<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'bail|required|string|max:255|unique:brands,name',
            'status' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام برند',
            'status' => 'وضعیت',
            'image' => 'تصویر',
            'description' => 'توضیحات',
            'categories' => 'دسته‌بندی‌ها',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام برند الزامی است.',
            'name.unique' => 'این برند قبلاً ثبت شده است.',
            'name.max' => 'نام برند نباید بیشتر از 255 کاراکتر باشد.',
            'image.image' => 'فایل انتخاب شده باید یک تصویر باشد.',
            'image.mimes' => 'فرمت تصویر معتبر نیست.',
            'image.max' => 'حجم تصویر نباید بیشتر از 2 مگابایت باشد.',
            'categories.*.exists' => 'دسته‌بندی انتخاب شده معتبر نیست.',
        ];
    }
}
