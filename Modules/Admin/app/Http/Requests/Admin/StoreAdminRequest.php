<?php

namespace Modules\Admin\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Permission\Models\Role;

class StoreAdminRequest extends FormRequest
{
    public function authorize()
    {
        // Adjust authorization as needed, or just return true for now
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'bail|required|string|max:255',
            'mobile' => 'bail|required|string|unique:admins,mobile|max:20',
            'role_id' => 'bail|required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $superAdminRoleId = Role::where('name', Role::SUPER_ADMIN)->value('id');
            if ($this->role_id == $superAdminRoleId) {
                $validator->errors()->add('role_id', 'نمی‌توانید نقش مدیر کل را به این ادمین اختصاص دهید.');
            }
        });
    }
}
