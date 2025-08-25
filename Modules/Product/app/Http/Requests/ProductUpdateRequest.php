<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends ProductStoreRequest
{
    /**
     * Constructor - calls parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Inherits from ProductStoreRequest and modifies as needed for updates.
     */
    public function rules(): array
    {
        // Get base rules from parent (ProductStoreRequest)
        $rules = parent::rules();

        // Modify rules for update context:

        // 1. Remove initial_stock rule (not applicable for updates)
        unset($rules['initial_stock']);

        // 2. Make images optional for updates (they're already nullable in parent)
        // No change needed as parent already has 'nullable'

        // 3. Add rules for removal operations
        $rules['remove_main_image'] = 'nullable|boolean';
        $rules['remove_gallery_images'] = 'nullable|array';
        $rules['remove_gallery_images.*'] = 'integer|exists:media,id';

        // 4. Make category_id required for updates (was nullable in store)
        $rules['category_id'] = 'required|exists:categories,id';

        // 5. Fix availability field name consistency
        if (isset($rules['availability_status'])) {
            $rules['availability'] = $rules['availability_status'];
            unset($rules['availability_status']);
        }

        // 6. Add brands validation (if not already present)
        if (!isset($rules['brands'])) {
            $rules['brands'] = 'nullable|array';
            $rules['brands.*'] = 'exists:brands,id';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     * Inherits from parent and adds update-specific attributes.
     */
    public function attributes(): array
    {
        // Get base attributes from parent
        $attributes = parent::attributes();

        // Remove attributes that don't apply to update
        unset($attributes['initial_stock']);

        // Add update-specific attributes
        $attributes['remove_main_image'] = 'حذف تصویر اصلی';
        $attributes['remove_gallery_images'] = 'تصاویر گالری برای حذف';
        $attributes['availability'] = 'وضعیت موجودی';
        $attributes['brands'] = 'برندها';
        $attributes['brands.*'] = 'برند';

        return $attributes;
    }

    /**
     * Get custom messages for validator errors.
     * Inherits from parent and adds update-specific messages.
     */
    public function messages(): array
    {
        // Get base messages from parent
        $messages = parent::messages();

        // Add update-specific messages
        $messages['remove_gallery_images.*.exists'] = 'تصویر انتخاب شده برای حذف معتبر نیست.';
        $messages['remove_gallery_images.*.integer'] = 'شناسه تصویر باید عدد باشد.';
        $messages['category_id.required'] = 'انتخاب دسته‌بندی الزامی است.';
        $messages['brands.*.exists'] = 'برند انتخاب شده معتبر نیست.';
        $messages['availability.required'] = 'انتخاب وضعیت موجودی الزامی است.';
        $messages['availability.in'] = 'وضعیت موجودی انتخاب شده معتبر نیست.';

        return $messages;
    }

    /**
     * Prepare the data for validation.
     * Inherits parent preparation and adds update-specific logic.
     */
    protected function prepareForValidation(): void
    {
        // Call parent preparation first
        if (method_exists(parent::class, 'prepareForValidation')) {
            parent::prepareForValidation();
        }

        // Update-specific preparations

        // Handle status checkbox (if not checked, it won't be in request)
        if (!$this->has('status')) {
            $this->merge(['status' => false]);
        }

        // Handle discount field
        if (!$this->has('discount') || $this->discount === '') {
            $this->merge(['discount' => 0]);
        }

        // Handle remove_main_image checkbox
        if (!$this->has('remove_main_image')) {
            $this->merge(['remove_main_image' => false]);
        }

        // Ensure arrays are properly formatted
        if ($this->has('remove_gallery_images') && !is_array($this->remove_gallery_images)) {
            $this->merge([
                'remove_gallery_images' => [$this->remove_gallery_images]
            ]);
        }

        // Fix field name mapping if needed
        if ($this->has('availability_status') && !$this->has('availability')) {
            $this->merge(['availability' => $this->availability_status]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation logic specific to updates

            // Validate that we're not removing main image and uploading new one simultaneously
            if ($this->boolean('remove_main_image') && $this->hasFile('main_image')) {
                $validator->errors()->add('main_image', 'نمی‌توانید همزمان تصویر اصلی را حذف و تصویر جدید آپلود کنید.');
            }

            // Validate gallery image removals belong to this product
            if ($this->has('remove_gallery_images')) {
                $product = $this->route('product');
                if ($product) {
                    $productMediaIds = $product->getMedia('gallery')->pluck('id')->toArray();
                    $removeIds = $this->get('remove_gallery_images', []);

                    foreach ($removeIds as $mediaId) {
                        if (!in_array($mediaId, $productMediaIds)) {
                            $validator->errors()->add('remove_gallery_images', 'یکی از تصاویر انتخاب شده برای حذف متعلق به این محصول نیست.');
                            break;
                        }
                    }
                }
            }
        });
    }
}
