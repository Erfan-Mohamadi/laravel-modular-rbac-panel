<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    private ProductStoreRequest $storeRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeRequest = new ProductStoreRequest();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [...$this->storeRequest->rules()];

        // Remove initial_stock validation for update (since it's only for create)
        unset($rules['initial_stock']);

        // Add validation for removing gallery images
        $rules['remove_gallery_images'] = 'nullable|array';
        $rules['remove_gallery_images.*'] = 'integer|exists:media,id';

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [...$this->storeRequest->attributes()];

        // Remove initial_stock attribute since we removed the rule
        unset($attributes['initial_stock']);

        // Add attribute for removing gallery images
        $attributes['remove_gallery_images'] = 'تصاویر گالری برای حذف';

        return $attributes;
    }

    public function messages(): array
    {
        $messages = [...$this->storeRequest->messages()];

        // Add messages for removing gallery images
        $messages['remove_gallery_images.*.exists'] = 'تصویر انتخاب شده برای حذف معتبر نیست.';

        return $messages;
    }
}
