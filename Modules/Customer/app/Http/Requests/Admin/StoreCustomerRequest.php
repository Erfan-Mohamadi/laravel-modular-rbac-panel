<?php

namespace Modules\Customer\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow admins to create customers
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => [
                'nullable',
                'email',
                'max:191',
                'unique:customers,email',
            ],
            'mobile' => [
                'required',
                'string',
                'max:20',
                'unique:customers,mobile',
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام مشتری الزامی است.',
            'email.email' => 'ایمیل نامعتبر است.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور حداقل باید ۶ کاراکتر باشد.',
            'password.confirmed' => 'تایید رمز عبور مطابقت ندارد.',
        ];
    }
}
