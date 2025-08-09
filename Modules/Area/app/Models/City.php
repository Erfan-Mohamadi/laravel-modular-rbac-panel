<?php

namespace Modules\Area\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class City extends Model
{
    protected $fillable = ['name', 'province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    protected static function booted()
    {
        static::created(fn () => Cache::forget('cities_list'));
        static::updated(fn () => Cache::forget('cities_list'));
        static::deleted(fn () => Cache::forget('cities_list'));
    }
}
