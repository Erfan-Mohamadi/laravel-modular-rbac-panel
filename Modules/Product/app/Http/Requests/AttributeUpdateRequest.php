<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeUpdateRequest extends FormRequest
{
    private AttributeStoreRequest $storeRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeRequest = new AttributeStoreRequest();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute')?->id;
        $rules = [...$this->storeRequest->rules()];

        $rules['name'] = [
            'required',
            'string',
            'max:255',
            'regex:/^[a-z0-9_]+$/',
            Rule::unique('attributes')->ignore($attributeId),
        ];

        unset($rules['type']);

        unset($rules['items']);

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [...$this->storeRequest->attributes()];



        return $attributes;
    }

    public function messages(): array
    {
        $messages = [...$this->storeRequest->messages()];


        return $messages;
    }

    protected function prepareForValidation(): void
    {
        $this->storeRequest->prepareForValidation();

        $this->merge([
            'name' => strtolower(str_replace([' ', '-'], '_', $this->name)),
            'status' => $this->boolean('status', true),
        ]);
    }
}
