<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * Relationship with specialty items (for select type)
     */
    public function items()
    {
        return $this->hasMany(SpecialtyItem::class);
    }

    /**
     * Relationship with categories (many-to-many)
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
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

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Check if specialty is select type
     */
    public function isSelectType()
    {
        return $this->type === 'select';
    }

    /**
     * Check if specialty is text type
     */
    public function isTextType()
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
