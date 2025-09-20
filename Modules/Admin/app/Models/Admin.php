<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Admin\Database\Factories\AdminFactory;
use Modules\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Symfony\Component\HttpKernel\Profiler\Profile;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     */

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    protected $guard_name = 'admin'; // Important!

    protected $fillable = [
        'name',
        'mobile',
        'password',
        'role_id',
        'status',
    ];

    public function getFormattedLastLoginDateAttribute()
    {
        return $this->last_login_date ? verta($this->last_login_date)->format('Y/m/d H:i') : '---';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_date' => 'datetime',
    ];

    /*------------------------------------
    | Relationships
    |-----------------------------------*/

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function username()
    {
        return 'mobile';
    }


    /*------------------------------------
    | Authentication Methods
    |-----------------------------------*/

    public function findForPassport($username)
    {
        return $this->where('mobile', $username)->first();
    }

    protected static function newFactory()
    {
        return AdminFactory::new();
    }
}
