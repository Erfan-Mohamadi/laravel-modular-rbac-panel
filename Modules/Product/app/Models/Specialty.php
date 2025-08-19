<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Category\Models\Category;

class Specialty extends Model
{
    use HasFactory;

    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    protected $fillable = [
        'name',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Many-to-many relationship with products (Product->N:M->Specialty)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_specialty')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Relationship with specialty items (for select type)
     */
    public function items(): HasMany
    {
        return $this->hasMany(SpecialtyItem::class);
    }

    /**
     * Many-to-many relationship with categories (Specialty->N:M->Category)
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }


    /**
     * Relationship with categories (many-to-many)
     */



    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Get products count for this specialty
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
     * Check if specialty has products
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Get unique specialty values used by products
     */
    public function getUsedValues(): array
    {
        return $this->products()
            ->wherePivot('value', '!=', null)
            ->get()
            ->pluck('pivot.value')
            ->unique()
            ->values()
            ->toArray();
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope to get only active specialties
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get only text type
     */
    public function scopeTextType($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope to get only select type
     */
    public function scopeSelectType($query)
    {
        return $query->where('type', 'select');
    }

    /**
     * Scope for specialties with products
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Scope for specialties with active products
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->active();
        });
    }

    /**
     * Scope for specialties in specific category
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Check if specialty is select type
     */
    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }

    /**
     * Check if specialty is text type
     */
    public function isTextType(): bool
    {
        return $this->type === 'text';
    }

    //======================================================================
    // FACTORY
    //======================================================================

    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\SpecialtyFactory::new();
    }
}
