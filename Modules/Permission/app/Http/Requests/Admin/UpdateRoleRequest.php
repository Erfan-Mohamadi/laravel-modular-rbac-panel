<?php

namespace Modules\Permission\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->input('role_id');

        if (!$roleId) {
            $segments = $this->segments();
            $rolesIndex = array_search('roles', $segments);
            if ($rolesIndex !== false && isset($segments[$rolesIndex + 1])) {
                $roleId = $segments[$rolesIndex + 1];
            }
        }

        $storeRequest = new StoreRoleRequest();
        $rules = $storeRequest->rules();

        if ($roleId) {
            $rules['name'] = 'bail|required|string|unique:roles,name,' . $roleId;
            $rules['role_id'] = 'required|exists:roles,id';
        }

        return $rules;
    }
}
