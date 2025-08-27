<?php

namespace Modules\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Area\Models\Province;

class Shipping extends Model
{
    // Explicit table name since your table is called shipping_table
    protected $table = 'shipping';

    // Mass assignable attributes
    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    /**
     * Provinces related to this shipping method (N:M)
     */
    public function provinces()
    {
        return $this->belongsToMany(Province::class, 'province_shipping')
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * Scope: only active shipping methods
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if shipping method is active
     */
    public function isActive(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Accessor for formatted image path
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/shippings/' . $this->image) : null;
    }
}
