<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Models\AttributeItem;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope for active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for text type attributes
     */
    public function scopeTextType($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope for select type attributes
     */
    public function scopeSelectType($query)
    {
        return $query->where('type', 'select');
    }

    /**
     * Get attribute items for select type attributes
     */
    public function attributeItems()
    {
        return $this->hasMany(AttributeItem::class);
    }

    /**
     * Get active attribute items
     */
    public function activeItems()
    {
        return $this->hasMany(AttributeItem::class);
    }

    /**
     * Get products that have this attribute
     */
    public function products()
    {
        // This will be implemented when you create the product_attributes pivot
        // return $this->belongsToMany(Product::class, 'product_attributes')
        //              ->withPivot('value')
        //              ->withTimestamps();
    }

    /**
     * Check if attribute is active
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Check if attribute is text type
     */
    public function isTextType(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if attribute is select type
     */
    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }
}
