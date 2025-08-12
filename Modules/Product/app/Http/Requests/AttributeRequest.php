<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handle authorization via middleware/policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $attributeId = $this->route('attribute')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/', // Only lowercase letters, numbers, and underscores
                Rule::unique('attributes')->ignore($attributeId),
            ],
            'label' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'in:select',
            ],
            'status' => [
                'boolean',
            ],
            // Attribute items validation (for select type)
            'items' => [
                'required_if:type,select',
                'string',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام فنی',
            'label' => 'برچسب',
            'type' => 'نوع ویژگی',
            'status' => 'وضعیت',
            'items' => 'مقادیر ویژگی',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'نام فنی فقط می‌تواند شامل حروف کوچک انگلیسی، اعداد و زیرخط باشد.',
            'name.unique' => 'یک ویژگی با این نام قبلاً ثبت شده است.',
            'label.required' => 'برچسب نمایش الزامی است.',
            'type.required' => 'انتخاب نوع ویژگی الزامی است.',
            'type.in' => 'نوع ویژگی فقط می‌تواند انتخابی باشد.',
            'items.required_if' => 'لطفاً مقدار(ها)ی ویژگی را وارد کنید.',
            'items.string' => 'هر مقدار ویژگی باید به صورت رشته وارد شود.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(str_replace([' ', '-'], '_', $this->name)),
            'status' => $this->boolean('status', true), // Default to true if not provided
        ]);
    }
}
