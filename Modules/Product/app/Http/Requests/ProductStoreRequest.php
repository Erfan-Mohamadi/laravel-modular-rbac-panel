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
            'title' => 'bail|required|string|max:255',
            'price' => 'bail|required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|lt:price',
            'availability_status' => 'bail|required|in:coming_soon,available,unavailable',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',

            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:specialties,id',
            'specialty_values' => 'nullable|array',
            'specialty_values.*' => 'nullable|string|max:255',
            'specialty_items' => 'nullable|array',
            'specialty_items.*' => 'nullable|array',
            'specialty_items.*.*' => 'exists:specialty_items,id',

            'initial_stock' => 'nullable|integer|min:0',

            'main_image' => 'nullable|image|mimes:jpeg,png,webp,jpg|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,webp,jpg|max:2048',
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
            'category_id' => 'دسته‌بندی',
            'specialties' => 'مشخصات',
            'specialty_values.*' => 'مقدار مشخصات',
            'specialty_items.*.*' => 'گزینه مشخصات',
            'initial_stock' => 'موجودی اولیه',
            'main_image' => 'تصویر اصلی',
            'gallery_images' => 'تصاویر گالری',
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

            // Image validation messages
            'main_image.image' => 'تصویر اصلی باید یک فایل تصویری باشد.',
            'main_image.mimes' => 'فرمت تصویر اصلی باید jpeg، png، webp یا jpg باشد.',
            'main_image.max' => 'حجم تصویر اصلی نمی‌تواند بیشتر از 2 مگابایت باشد.',
            'gallery_images.*.image' => 'تمام فایل‌های گالری باید تصویر باشند.',
            'gallery_images.*.mimes' => 'فرمت تصاویر گالری باید jpeg، png، webp یا jpg باشد.',
            'gallery_images.*.max' => 'حجم هر تصویر گالری نمی‌تواند بیشتر از 2 مگابایت باشد.',
        ];
    }
}
