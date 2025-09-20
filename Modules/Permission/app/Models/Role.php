<?php

namespace Modules\Permission\Models;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $attributes = [
        'guard_name' => 'admin',
    ];

    public const SUPER_ADMIN = 'super_admin';

    protected static function booted()
    {
        static::created(function () {
            self::clearAllCaches();
        });

        static::updated(function () {
            self::clearAllCaches();
        });

        static::deleted(function () {
            self::clearAllCaches();
        });
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

    /**
     * Clear all related caches including Spatie permission cache
     */
    public static function clearAllCaches()
    {
        // Clear our custom cache
        self::clearCache();

        // Clear Spatie permission cache
        self::clearSpatieCache();

        // Clear Permission model cache if it exists
        if (class_exists('\Modules\Permission\Models\Permission')) {
            \Modules\Permission\Models\Permission::clearCache();
        }
    }

    /**
     * Clear Spatie permission cache
     */
    private static function clearSpatieCache()
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // Silently handle if service is not available
            \Log::warning('Could not clear Spatie permission cache: ' . $e->getMessage());
        }
    }

    /**
     * Get fresh roles without cache
     */
    public static function freshRoles()
    {
        return self::select('id', 'name', 'label', 'created_at')->latest('id')->get();
    }
}
