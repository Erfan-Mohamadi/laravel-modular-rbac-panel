<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateRequest extends FormRequest
{
    private StoreStoreRequest $storeRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeRequest = new StoreStoreRequest();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store')?->id;
        $rules = [...$this->storeRequest->rules()];

        $rules['product_id'] = [
            'required',
            'exists:products,id',
            Rule::unique('stores', 'product_id')->ignore($storeId)
        ];

        $rules['balance'] = [
            'required',
            'integer',
            'min:0',
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

        // Add only essential validation messages
        $messages['product_id.unique'] = 'این محصول قبلاً در انبار ثبت شده است.';
        $messages['balance.min'] = 'موجودی نمی‌تواند منفی باشد.';

        return $messages;
    }
}
