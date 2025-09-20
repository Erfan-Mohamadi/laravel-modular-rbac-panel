<?php

namespace Modules\Admin\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Permission\Models\Role;

class StoreAdminRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'mobile' => [
                'bail',
                'required',
                'string',
                'max:20',
                Rule::unique('admins', 'mobile'),
            ],
            'role_id' => [
                'bail',
                'required',
                'exists:roles,id',
            ],
            'status' => [
                'required',
                'boolean',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'نام ادمین الزامی است.',
            'name.string' => 'نام باید به صورت متن باشد.',
            'name.max' => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',

            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.string' => 'شماره موبایل باید به صورت متن باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'mobile.max' => 'شماره موبایل نمی‌تواند بیشتر از 20 کاراکتر باشد.',

            'role_id.required' => 'انتخاب نقش الزامی است.',
            'role_id.exists' => 'نقش انتخاب شده معتبر نیست.',

            'status.required' => 'وضعیت الزامی است.',
            'status.boolean' => 'وضعیت باید true یا false باشد.',

            'password.required' => 'رمز عبور الزامی است.',
            'password.string' => 'رمز عبور باید به صورت متن باشد.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $superAdminRoleId = Role::query()->where('name', Role::SUPER_ADMIN)->value('id');
            if ($this->role_id == $superAdminRoleId) {
                $validator->errors()->add('role_id', 'نمی‌توانید نقش مدیر کل را به این ادمین اختصاص دهید.');
            }
        });
    }
}
