<?php

namespace Modules\Area\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\Customer\Models\Address;
use Modules\Shipping\Models\Shipping; // Add this

class Province extends Model
{
    protected $fillable = ['name'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    /**
     * Shippings related to this province
     */
    public function shippings()
    {
        return $this->belongsToMany(Shipping::class, 'province_shipping')
            ->withPivot('price')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('provinces');
            Cache::rememberForever('provinces', fn () => static::query()
                ->select('id', 'name')
                ->latest('id')->get());
        });

        static::deleted(function () {
            Cache::forget('provinces');
            Cache::rememberForever('provinces', fn () => static::query()
                ->select('id', 'name')
                ->latest('id')->get());
        });
    }
}
