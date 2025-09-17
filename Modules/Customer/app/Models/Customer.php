<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        'otp_expires_at' => 'datetime',
        'status' => 'boolean',
    ];

    // Relationship to addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->wallet()->create([
                'balance' => 0,
            ]);
        });
    }
}
