<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shipping,name',
            'status' => 'boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048',
            'provinces.*.selected' => 'nullable|boolean',
            'provinces.*.price' => 'nullable|required_with:provinces.*.selected|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام حمل و نقل الزامی است.',
            'name.unique' => 'این نام قبلاً استفاده شده است.',
            'name.max' => 'نام حمل و نقل نباید بیشتر از 255 کاراکتر باشد.',
            'icon.image' => 'فایل آپلود شده باید عکس باشد.',
            'icon.mimes' => 'فرمت عکس باید png، jpeg، jpg یا gif باشد.',
            'icon.max' => 'حجم عکس نباید بیشتر از 2 مگابایت باشد.',
            'provinces.*.price.required_with' => 'قیمت برای استان انتخاب شده الزامی است.',
            'provinces.*.price.numeric' => 'قیمت باید عدد باشد.',
            'provinces.*.price.min' => 'قیمت نمی‌تواند منفی باشد.',
        ];
    }
}
