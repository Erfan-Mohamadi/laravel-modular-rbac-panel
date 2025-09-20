<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:100',
            'province_id' => 'bail|required|exists:provinces,id',
            'city_id' => 'bail|required|exists:cities,id',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'bail|required|string|max:20',
            'address_line' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'province_id.required' => 'استان الزامی است.',
            'province_id.exists'   => 'استان انتخاب شده معتبر نیست.',
            'city_id.required'     => 'شهر الزامی است.',
            'city_id.exists'       => 'شهر انتخاب شده معتبر نیست.',
            'postal_code.required' => 'کد پستی الزامی است.',
        ];
    }
}
