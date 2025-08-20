<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialtyItem extends Model
{
    use HasFactory;

    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'specialty_id',
        'value'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Get the specialty that owns this item.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Get all product specialty pivot records that use this item
     */
    public function productSpecialties(): HasMany
    {
        return $this->hasMany(ProductSpecialty::class, 'specialty_item_id');
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Get count of products using this specialty item
     */
    public function getUsageCountAttribute(): int
    {
        return \DB::table('product_specialty')
            ->where('specialty_item_id', $this->id)
            ->count();
    }

    /**
     * Check if this item is being used by any products
     */
    public function isUsed(): bool
    {
        return \DB::table('product_specialty')
            ->where('specialty_item_id', $this->id)
            ->exists();
    }

    /**
     * Get products that use this specialty item
     */
    public function getUsedByProducts()
    {
        return Product::whereHas('specialties', function ($query) {
            $query->wherePivot('specialty_item_id', $this->id);
        });
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope for items of a specific specialty
     */
    public function scopeForSpecialty($query, $specialtyId)
    {
        return $query->where('specialty_id', $specialtyId);
    }

    /**
     * Scope for items that are being used
     */
    public function scopeUsed($query)
    {
        return $query->whereHas('productSpecialties');
    }

    /**
     * Scope for items that are not being used
     */
    public function scopeUnused($query)
    {
        return $query->whereDoesntHave('productSpecialties');
    }


}
