<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecialtyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialty_id',
        'value'
    ];

    // Relationship with specialty
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\SpecialtyItemFactory::new();
    }
}
