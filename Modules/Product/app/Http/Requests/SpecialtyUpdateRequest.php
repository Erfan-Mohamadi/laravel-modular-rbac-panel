<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialtyUpdateRequest extends FormRequest
{
    private SpecialtyStoreRequest $storeRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeRequest = new SpecialtyStoreRequest();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $specialtyId = $this->route('specialty')?->id;
        $rules = [...$this->storeRequest->rules()];

        // Override the name rule to add unique validation with ignore for update
        $rules['name'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('specialties', 'name')->ignore($specialtyId)
        ];
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
        $messages['name.unique'] = 'یک ویژگی با این نام قبلاً ثبت شده است.';


        return $messages;
    }
}
