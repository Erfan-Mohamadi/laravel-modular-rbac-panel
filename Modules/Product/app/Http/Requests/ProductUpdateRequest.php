<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    protected ProductStoreRequest $storeRequest;

    public function __construct(ProductStoreRequest $storeRequest)
    {
        parent::__construct();
        $this->storeRequest = $storeRequest;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Inherit rules from ProductStoreRequest
        $rules = $this->storeRequest->rules();

        // Remove rules that only apply to store
        unset($rules['initial_stock']);

        // Make images optional in update (not required)
        $rules['main_image'] = 'nullable|image|mimes:jpeg,png,webp,jpg|max:2048';
        $rules['gallery_images'] = 'nullable|array';
        $rules['gallery_images.*'] = 'image|mimes:jpeg,png,webp,jpg|max:2048';

        // Add support for removing gallery images
        $rules['remove_gallery_images'] = 'nullable|array';
        $rules['remove_gallery_images.*'] = 'integer|exists:media,id';

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = $this->storeRequest->attributes();

        // Remove attributes that don’t apply to update
        unset($attributes['initial_stock']);

        // Add new attributes
        $attributes['remove_gallery_images'] = 'تصاویر گالری برای حذف';

        return $attributes;
    }

    public function messages(): array
    {
        $messages = $this->storeRequest->messages();

        // Add custom messages for removing gallery images
        $messages['remove_gallery_images.*.exists'] = 'تصویر انتخاب شده برای حذف معتبر نیست.';

        return $messages;
    }
}
