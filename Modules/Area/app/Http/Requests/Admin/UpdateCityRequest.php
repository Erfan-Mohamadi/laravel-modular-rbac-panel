<?php

namespace Modules\Area\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    protected $storeRequest;

    public function __construct(StoreCityRequest $storeRequest)
    {
        parent::__construct();
        $this->storeRequest = $storeRequest;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = $this->storeRequest->rules();
        return $rules;
    }
}
