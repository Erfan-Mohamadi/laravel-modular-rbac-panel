<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Product;
use Modules\Product\Models\Specialty;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'icon',
        'parent_id'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * One-to-many relationship with products (Product->N:1->Category)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Many-to-many relationship with brands (Brand->N:M->Category)
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_category')
            ->withTimestamps();
    }

    /**
     * Many-to-many relationship with specialties (Specialty->N:M->Category)
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'category_specialty')
            ->withTimestamps();
    }

    /**
     * Parent category relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Children categories relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Get products count for this category
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
     * Get all products including from subcategories
     */
    public function getAllProducts()
    {
        $allCategoryIds = $this->getAllDescendantIds();
        $allCategoryIds[] = $this->id;

        return Product::whereIn('category_id', $allCategoryIds);
    }

    /**
     * Get all descendant category IDs
     */
    public function getAllDescendantIds(): array
    {
        $descendants = [];

        foreach ($this->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $child->getAllDescendantIds());
        }

        return $descendants;
    }

    /**
     * Check if category has products
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Check if category has active products
     */
    public function hasActiveProducts(): bool
    {
        return $this->products()->active()->exists();
    }

    /**
     * Check if category is root (has no parent)
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if category has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get breadcrumb path
     */
    public function getBreadcrumbPath(): array
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category);
            $category = $category->parent;
        }

        return $path;
    }

    /**
     * Get category level (depth in hierarchy)
     */
    public function getLevel(): int
    {
        return count($this->getBreadcrumbPath()) - 1;
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope for root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for categories with products
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Scope for categories with active products
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->active();
        });
    }

    /**
     * Scope for categories with specific brand
     */
    public function scopeWithBrand($query, $brandId)
    {
        return $query->whereHas('brands', function ($q) use ($brandId) {
            $q->where('brands.id', $brandId);
        });
    }

    /**
     * Scope for categories with specific specialty
     */
    public function scopeWithSpecialty($query, $specialtyId)
    {
        return $query->whereHas('specialties', function ($q) use ($specialtyId) {
            $q->where('specialties.id', $specialtyId);
        });
    }
}
