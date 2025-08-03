<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Admin\Database\Factories\AdminFactory;
use Spatie\Permission\Traits\HasRoles;
use Symfony\Component\HttpKernel\Profiler\Profile;

// use Modules\Admin\Database\Factories\AdminFactory;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     */

    protected $guard = 'admin';
    protected $fillable = [
        'name',
        'mobile',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
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
