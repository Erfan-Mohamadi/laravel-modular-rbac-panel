<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'shipping_id' => 'required|integer|exists:shippings,id',
            'cart_items' => 'sometimes|array|min:1',
            'cart_items.*.cart_id' => 'required_with:cart_items|integer|exists:cart,id',
            'cart_items.*.quantity' => 'required_with:cart_items|integer|min:1',
            'address_id' => 'required|exists:addresses,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'shipping_id.required' => 'شناسه حمل و نقل الزامی است.',
            'shipping_id.exists' => 'شناسه حمل و نقل معتبر نیست.',
            'cart_items.required' => 'حداقل یک کالا برای سفارش لازم است.',
            'cart_items.*.cart_id.required' => 'شناسه کالای سبد خرید الزامی است.',
            'cart_items.*.cart_id.exists' => 'کالای سبد خرید یافت نشد.',
            'cart_items.*.quantity.required' => 'تعداد کالا الزامی است.',
            'cart_items.*.quantity.min' => 'تعداد کالا باید حداقل 1 باشد.',
        ];
    }
}

