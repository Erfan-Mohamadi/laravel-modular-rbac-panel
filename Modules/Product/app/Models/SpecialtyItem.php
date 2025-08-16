<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecialtyItem extends Model
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
        'specialty_id',
        'value'
    ];

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Get the specialty that owns this item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    //======================================================================
    // FACTORY
    //======================================================================

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\SpecialtyItemFactory::new();
    }
}
