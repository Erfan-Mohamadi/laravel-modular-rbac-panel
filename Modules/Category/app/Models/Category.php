<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Brand;

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

    /**
     * Many-to-many relationship with brands
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_category');
    }

    /**
     * Parent category relationship
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Children categories relationship
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

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
}
