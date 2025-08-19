<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Category\Models\Category;

class Brand extends Model
{
    use HasFactory;

    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    protected $fillable = [
        'name',
        'status',
        'image',
        'description'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * One-to-many relationship with products (Brand->1:M->Product)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Many-to-many relationship with categories (Brand->N:M->Category)
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'brand_category')
            ->withTimestamps();
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Get products count for this brand
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->products()->active()->count();
    }

    /**
     * Get available products count
     */
    public function getAvailableProductsCountAttribute(): int
    {
        return $this->products()->available()->count();
    }

    /**
     * Check if brand has products
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Check if brand has active products
     */
    public function hasActiveProducts(): bool
    {
        return $this->products()->active()->exists();
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope for active brands
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for brands with products
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Scope for brands with active products
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->active();
        });
    }

    /**
     * Scope for brands in specific category
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    //======================================================================
    // ACCESSORS
    //======================================================================

    /**
     * Get the brand's image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
