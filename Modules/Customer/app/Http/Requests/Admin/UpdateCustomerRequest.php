<?php

namespace Modules\Customer\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can add admin permission check here if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'mobile' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'mobile')->ignore($customerId),
            ],
            'password' => 'nullable|string|min:6|confirmed', // optional, only if changing
            'status' => 'required|boolean',
        ];
    }

    /**
     * Customize the field names in error messages (optional)
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'email' => 'ایمیل',
            'mobile' => 'موبایل',
            'password' => 'رمز عبور',
            'status' => 'وضعیت',
        ];
    }
}
