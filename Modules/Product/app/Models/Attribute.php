<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    protected $fillable = [
        'name',
        'label',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope a query to only include active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include text type attributes
     */
    public function scopeTextType($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope a query to only include select type attributes
     */
    public function scopeSelectType($query)
    {
        return $query->where('type', 'select');
    }

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Get all attribute items for this attribute
     */
    public function attributeItems()
    {
        return $this->hasMany(AttributeItem::class);
    }

    /**
     * Get active attribute items for this attribute
     */
    public function activeItems()
    {
        return $this->hasMany(AttributeItem::class);
    }

    /**
     * Get products associated with this attribute
     *
     * @todo Implement when product_attributes pivot table is created
     */
    public function products()
    {
        // Implementation pending pivot table creation
        // return $this->belongsToMany(Product::class, 'product_attributes')
        //     ->withPivot('value')
        //     ->withTimestamps();
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================

    /**
     * Check if the attribute is active
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Check if the attribute is of text type
     */
    public function isTextType(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if the attribute is of select type
     */
    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }
}
