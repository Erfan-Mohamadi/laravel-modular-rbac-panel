<?php
// Modules/Product/Models/Product.php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Modules\Store\Models\Store;
use Modules\Category\Models\Category;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'discount',
        'availability_status',
        'status',
        'description'
    ];

    protected $casts = [
        'price' => 'integer',
        'discount' => 'integer',
        'status' => 'boolean'
    ];


    // Availability status constants
    const COMING_SOON = 'coming_soon';
    const AVAILABLE = 'available';
    const UNAVAILABLE = 'unavailable';

    // Get all availability statuses
    public static function getAvailabilityStatuses()
    {
        return [
            'available' => 'موجود',
            'coming_soon' => 'به زودی',
            'unavailable' => 'ناموجود',
        ];
    }

    // One-to-One relationship with Store (Product owns Store)
    public function store()
    {
        return $this->hasOne(Store::class);
    }

    // Many-to-Many relationship with Categories
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // Many-to-Many relationship with Specialties
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('status', false);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->where('availability_status', self::AVAILABLE);
    }

    public function scopeComingSoon(Builder $query)
    {
        return $query->where('availability_status', self::COMING_SOON);
    }

    public function scopeUnavailable(Builder $query)
    {
        return $query->where('availability_status', self::UNAVAILABLE);
    }

    public function scopeByAvailabilityStatus(Builder $query, string $status)
    {
        return $query->where('availability_status', $status);
    }

    // Accessors & Mutators
    public function getFinalPriceAttribute()
    {
        $finalPrice = $this->price - $this->discount;
        return max($finalPrice, 0); // Ensure price doesn't go below 0
    }

    public function getDiscountAmountAttribute()
    {
        return $this->discount;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->price > 0 && $this->discount > 0) {
            return round(($this->discount / $this->price) * 100, 2);
        }
        return 0;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->discount > 0;
    }

    public function getAvailabilityStatusLabelAttribute()
    {
        return self::getAvailabilityStatuses()[$this->availability_status] ?? 'Unknown';
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    // Helper Methods
    public function isComingSoon()
    {
        return $this->availability_status === self::COMING_SOON;
    }

    public function isAvailable()
    {
        return $this->availability_status === self::AVAILABLE;
    }

    public function isUnavailable()
    {
        return $this->availability_status === self::UNAVAILABLE;
    }

    public function activate()
    {
        $this->update(['status' => true]);
    }

    public function deactivate()
    {
        $this->update(['status' => false]);
    }

    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\ProductFactory::new();
    }
}
