<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * Many-to-many relationship with categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category');
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
