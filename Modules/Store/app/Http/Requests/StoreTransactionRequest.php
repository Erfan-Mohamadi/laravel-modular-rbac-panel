<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:increase,decrease',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'محصول',
            'amount' => 'مقدار',
            'type' => 'نوع تراکنش',
            'description' => 'توضیحات',
        ];
    }
}
