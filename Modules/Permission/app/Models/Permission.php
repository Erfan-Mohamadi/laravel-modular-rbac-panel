<?php

namespace Modules\Permission\Models;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $guard_name = 'admin';
    protected $table = 'permissions';

    protected static function booted()
    {
        static::created(fn () => self::clearCache());
        static::updated(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function cachedPermissions()
    {
        return Cache::rememberForever('cached_permissions', function () {
            return self::latest('id')->get();
        });
    }

    public static function clearCache()
    {
        Cache::forget('cached_permissions');
    }
}
