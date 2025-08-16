<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialtyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,select',
            'status' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'items' => 'required_if:type,select|array',
            'items.*' => 'required|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام ویژگی',
            'type' => 'نوع ویژگی',
            'status' => 'وضعیت',
            'categories' => 'دسته‌بندی‌ها',
            'items' => 'مقادیر ویژگی',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required_if' => 'لطفاً مقدار(ها)ی ویژگی را وارد کنید.',
            'items.*.required' => 'هر مقدار ویژگی نمی‌تواند خالی باشد.',
            'items.*.max' => 'هر مقدار ویژگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ];
    }
}
