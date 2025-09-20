<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('attributes'),
            ],
            'label' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'bail',
                'required',
                'in:select',
            ],
            'status' => [
                'boolean',
            ],
            'items' => [
                'required_if:type,select',
                'string',
            ],
        ];
    }

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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(str_replace([' ', '-'], '_', $this->name)),
            'status' => $this->boolean('status', true),
        ]);
    }
}
