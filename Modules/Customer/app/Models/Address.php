<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Area\Models\City;
use Modules\Area\Models\Province;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'title',
        'province_id',
        'city_id',
        'district',
        'postal_code',
        'address_line',
    ];

    protected $with = ['province', 'city'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
