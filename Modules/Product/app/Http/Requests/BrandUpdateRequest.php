<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandUpdateRequest extends FormRequest
{
    private BrandStoreRequest $storeRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeRequest = new BrandStoreRequest();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id;
        $rules = [...$this->storeRequest->rules()];

        $rules['name'] = 'required|string|max:255|unique:brands,name,' . $brandId;

        return $rules;
    }

    public function attributes(): array
    {
        return [...$this->storeRequest->attributes()];
    }

    public function messages(): array
    {
        return [...$this->storeRequest->messages()];
    }
}
