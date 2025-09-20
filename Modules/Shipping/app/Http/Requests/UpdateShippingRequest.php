<?php

namespace Modules\Shipping\Http\Requests;

class UpdateShippingRequest extends StoreShippingRequest
{
    protected $shipping;

    /**
     * Set the shipping model for unique validation.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Get the shipping ID from route parameter
        $shippingId = $this->route('shipping')?->id ?? $this->route('shipping');

        // Update the unique rule for name to exclude current shipping
        $rules['name'] = 'required|string|max:255|unique:shipping,name,' . $shippingId;

        return $rules;
    }
}
