<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\Attribute;

class AttributeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value'
    ];

    /**
     * Get the attribute that owns this item
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Scope for specific attribute
     */
    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }
}
