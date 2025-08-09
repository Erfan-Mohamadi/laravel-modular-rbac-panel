<?php

namespace Modules\Area\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Province extends Model
{
    protected $fillable = ['name'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('provinces');
            Cache::rememberForever('provinces', fn () => static::all());
        });

        static::deleted(function () {
            Cache::forget('provinces');
            Cache::rememberForever('provinces', fn () => static::all());
        });
    }
}
