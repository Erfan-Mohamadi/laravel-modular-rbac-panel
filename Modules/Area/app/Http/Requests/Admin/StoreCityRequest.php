<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'bail|required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
        ];
    }
}
