<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeItem extends Model
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
        'attribute_id',
        'value'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Get the parent attribute that owns this item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    //======================================================================
    // SCOPES
    //======================================================================

    /**
     * Scope a query to only include items for a specific attribute
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $attributeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    //======================================================================
    // ACCESSORS/MUTATORS
    //======================================================================
    
}
