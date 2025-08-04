<?php

namespace Modules\Permission\Models;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $attributes = [
        'guard_name' => 'admin',
    ];

    public const SUPER_ADMIN = 'super_admin';

    protected static function booted()
    {
        static::created(fn () => self::clearCache());
        static::updated(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function cachedRoles()
    {
        return Cache::rememberForever('cached_roles', function () {
            return self::select('id', 'name', 'label', 'created_at')->latest('id')->get();
        });
    }

    public static function clearCache()
    {
        Cache::forget('cached_roles');
    }
}
