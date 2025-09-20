<?php

namespace Modules\Category\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'نام دسته‌بندی الزامی است.',
            'name.string'   => 'نام دسته‌بندی باید متن باشد.',
            'name.max'      => 'نام دسته‌بندی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'name.unique'   => 'این نام دسته‌بندی قبلاً ثبت شده است.',
            'parent_id.exists' => 'دسته والد انتخاب شده وجود ندارد.',
            'status.required' => 'انتخاب وضعیت الزامی است.',
            'status.boolean' => 'وضعیت باید صحیح یا غلط باشد.',
            'icon.image'    => 'آیکون باید یک فایل تصویر باشد.',
            'icon.mimes'    => 'آیکون باید از نوع فایل‌های png، jpeg، jpg یا gif باشد.',
            'icon.max'      => 'حجم آیکون نباید بیشتر از ۲ مگابایت باشد.',
        ];
    }
}
