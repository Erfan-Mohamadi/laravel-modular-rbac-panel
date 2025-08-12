<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust this if you add authorization logic later
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Getting the attribute and item route parameters
        $attribute = $this->route('attribute');
        $item = $this->route('item'); // null if creating new

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_items')
                    ->where('attribute_id', $attribute->id)
                    ->ignore($item?->id),
            ],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'value' => 'مقدار',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'value.required' => 'وارد کردن مقدار الزامی است.',
            'value.unique' => 'این مقدار قبلاً برای این ویژگی ثبت شده است.',
            'value.max' => 'مقدار نباید بیشتر از 255 کاراکتر باشد.',
        ];
    }
}
