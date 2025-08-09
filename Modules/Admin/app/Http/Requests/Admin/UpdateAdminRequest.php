<?php

namespace Modules\Admin\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Permission\Models\Role;

class UpdateAdminRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $adminId = $this->route('admin')->id; // get current admin id from route

        return [
            'name' => 'required|string|max:255',
            'mobile' => "required|string|max:20|unique:admins,mobile,{$adminId}",
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
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
