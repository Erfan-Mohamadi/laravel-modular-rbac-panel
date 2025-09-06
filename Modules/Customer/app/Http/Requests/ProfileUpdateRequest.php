<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // You can add permission logic if needed
    }

    public function rules(): array
    {
        $customerId = $this->user()->id;

        return [
            'name'     => 'nullable|string|max:100',
            'email'    => 'nullable|email|max:191|unique:customers,email,' . $customerId,
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'      => 'ایمیل وارد شده معتبر نیست.',
            'email.unique'     => 'این ایمیل قبلاً استفاده شده است.',
            'password.min'     => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'password.confirmed' => 'تأیید رمز عبور مطابقت ندارد.',
        ];
    }
}
