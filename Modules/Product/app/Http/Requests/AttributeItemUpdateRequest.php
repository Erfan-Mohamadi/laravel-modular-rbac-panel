<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attribute = $this->route('attribute');
        $item = $this->route('item');

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_items')
                    ->where('attribute_id', $attribute->id)
                    ->ignore($item->id),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['value' => 'مقدار'];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'وارد کردن مقدار الزامی است.',
            'value.unique' => 'این مقدار قبلاً برای این ویژگی ثبت شده است.',
            'value.max' => 'مقدار نباید بیشتر از 255 کاراکتر باشد.',
        ];
    }
}
