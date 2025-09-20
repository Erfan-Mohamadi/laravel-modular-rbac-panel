<?php

namespace Modules\Category\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Category\Models\Category;

class UpdateCategoryRequest extends FormRequest
{
    protected $category;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the category being updated
     */
    protected function getCategory()
    {
        if (!$this->category) {
            $this->category = $this->route('category');
        }
        return $this->category;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $category = $this->getCategory();
        $categoryId = $category instanceof Category ? $category->id : $category;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value == $categoryId) {
                        $fail('دسته‌بندی نمی‌تواند والد خودش باشد.');
                    }
                    if ($value && $this->wouldCreateCircularReference($categoryId, $value)) {
                        $fail('نمی‌توان این دسته‌بندی را به عنوان والد انتخاب کرد زیرا باعث ایجاد مرجع دایره‌ای می‌شود.');
                    }
                },
            ],
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

    /**
     * Check if setting this parent would create a circular reference
     */
    private function wouldCreateCircularReference($categoryId, $parentId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return false;
        }

        $descendantIds = $category->getAllDescendantIds();
        return in_array($parentId, $descendantIds);
    }
}
