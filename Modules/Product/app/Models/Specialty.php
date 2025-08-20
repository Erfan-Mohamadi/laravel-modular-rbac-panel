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

    // Type constants
    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Many-to-many relationship with products (Product->N:M->Specialty)
     * Updated to include specialty_item_id in pivot
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_specialty')
            ->withPivot(['value', 'specialty_item_id'])
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
     * Get active specialty items
     */
    public function activeItems(): HasMany
    {
        return $this->items(); // Add any active scope if needed later
    }

    /**
     * Many-to-many relationship with categories (Specialty->N:M->Category)
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_specialty');
    }

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
     * Get unique specialty values used by products (text values only)
     */
    public function getUsedValues(): array
    {
        return $this->products()
            ->wherePivot('value', '!=', null)
            ->wherePivot('specialty_item_id', null) // Only custom text values
            ->get()
            ->pluck('pivot.value')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get used specialty items (for select type specialties)
     */
    public function getUsedItems(): array
    {
        return $this->products()
            ->wherePivot('specialty_item_id', '!=', null)
            ->get()
            ->pluck('pivot.specialty_item_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if specialty is select type
     */
    public function isSelectType(): bool
    {
        return $this->type === self::TYPE_SELECT;
    }

    /**
     * Check if specialty is text type
     */
    public function isTextType(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Get type label in Persian
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_TEXT ? 'متن' : 'انتخابی';
    }

    /**
     * Get items count for select type specialties
     */
    public function getItemsCountAttribute(): int
    {
        return $this->isSelectType() ? $this->items()->count() : 0;
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
        return $query->where('type', self::TYPE_TEXT);
    }

    /**
     * Scope to get only select type
     */
    public function scopeSelectType($query)
    {
        return $query->where('type', self::TYPE_SELECT);
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

    /**
     * Scope for specialties with items (select type with predefined options)
     */
    public function scopeWithItems($query)
    {
        return $query->selectType()->whereHas('items');
    }

}
