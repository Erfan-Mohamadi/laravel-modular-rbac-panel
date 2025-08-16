<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'balance' => 'required|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'محصول',
            'balance' => 'موجودی',
        ];
    }
}
