<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecialty extends Model
{
    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    protected $table = 'product_specialty';

    protected $fillable = [
        'product_id',
        'specialty_id',
        'value',
        'specialty_item_id'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'specialty_id' => 'integer',
        'specialty_item_id' => 'integer',
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Get the product that owns this specialty relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the specialty for this relationship
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Get the specialty item if one is selected
     */
    public function specialtyItem(): BelongsTo
    {
        return $this->belongsTo(SpecialtyItem::class);
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Get the display value for this specialty
     * Returns either the specialty item value or the custom text value
     */
    public function getDisplayValueAttribute(): ?string
    {
        if ($this->specialty_item_id && $this->specialtyItem) {
            return $this->specialtyItem->value;
        }

        return $this->value;
    }

    /**
     * Check if this uses a predefined specialty item
     */
    public function usesSpecialtyItem(): bool
    {
        return !is_null($this->specialty_item_id);
    }

    /**
     * Check if this uses a custom text value
     */
    public function usesCustomValue(): bool
    {
        return !is_null($this->value) && is_null($this->specialty_item_id);
    }

    /**
     * Get the specialty type
     */
    public function getSpecialtyTypeAttribute(): string
    {
        return $this->specialty->type ?? '';
    }

    /**
     * Get formatted data for API responses
     */
    public function getFormattedDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'specialty_id' => $this->specialty_id,
            'specialty_name' => $this->specialty->name ?? '',
            'specialty_type' => $this->specialty->type ?? '',
            'value' => $this->value,
            'specialty_item_id' => $this->specialty_item_id,
            'specialty_item_value' => $this->specialtyItem?->value,
            'display_value' => $this->display_value,
            'uses_item' => $this->usesSpecialtyItem(),
            'uses_custom' => $this->usesCustomValue()
        ];
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope for records with custom text values
     */
    public function scopeWithCustomValue($query)
    {
        return $query->whereNotNull('value')
            ->whereNull('specialty_item_id');
    }

    /**
     * Scope for records with specialty items
     */
    public function scopeWithSpecialtyItem($query)
    {
        return $query->whereNotNull('specialty_item_id');
    }

    /**
     * Scope for specific specialty
     */
    public function scopeForSpecialty($query, $specialtyId)
    {
        return $query->where('specialty_id', $specialtyId);
    }

    /**
     * Scope for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
