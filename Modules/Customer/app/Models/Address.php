<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'province',
        'city',
        'district',
        'postal_code',
        'address_line',
        'latitude',
        'longitude',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
