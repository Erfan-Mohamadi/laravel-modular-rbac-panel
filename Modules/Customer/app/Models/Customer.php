<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'mobile_verified_at',
        'otp',
        'otp_expires_at',
        'last_login_date',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'mobile_verified_at' => 'timestamp',
        'otp_expires_at' => 'datetime', // Laravel will convert DB value to Carbon
        'status' => 'boolean',

    ];

    // Relationship to addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
