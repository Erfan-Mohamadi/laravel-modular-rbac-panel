<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CartStoreRequest extends FormRequest
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
            'product_id' => 'bail|required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'شناسه محصول الزامی است.',
            'product_id.integer'  => 'شناسه محصول باید یک عدد صحیح باشد.',
            'product_id.exists'   => 'محصول انتخاب‌شده وجود ندارد.',

            'quantity.required'   => 'تعداد الزامی است.',
            'quantity.integer'    => 'تعداد باید یک عدد صحیح باشد.',
            'quantity.min'        => 'تعداد باید حداقل ۱ باشد.',
            'quantity.max'        => 'تعداد نمی‌تواند بیشتر از ۹۹۹ باشد.',

            'price.required'      => 'قیمت الزامی است.',
            'price.integer'       => 'قیمت باید یک عدد صحیح باشد.',
            'price.min'           => 'قیمت باید بزرگ‌تر یا مساوی ۰ باشد.',
        ];
    }


    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'اعتبارسنجی ناموفق بود.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
