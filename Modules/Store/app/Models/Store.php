<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Modules\Product\Models\Product;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'balance'
    ];

    protected $casts = [
        'balance' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions()
    {
        return $this->hasMany(StoreTransaction::class);
    }

    // Scopes
    public function scopeWithPositiveBalance(Builder $query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeWithZeroBalance(Builder $query)
    {
        return $query->where('balance', 0);
    }

    public function scopeWithNegativeBalance(Builder $query)
    {
        return $query->where('balance', '<', 0);
    }

    // Helper Methods
    public function addStock($amount, $description = null)
    {
        $this->increment('balance', $amount);

        $this->transactions()->create([
            'type' => 'increment',
            'count' => $amount,
            'description' => $description
        ]);

        return $this;
    }

    public function subtractStock($amount, $description = null)
    {
        $this->decrement('balance', $amount);

        $this->transactions()->create([
            'type' => 'decrement',
            'count' => $amount,
            'description' => $description
        ]);

        return $this;
    }

    public function addTransaction($type, $amount, $description = null)
    {
        if ($type === 'increment') {
            $this->addStock($amount, $description);
        } else {
            $this->subtractStock($amount, $description);
        }

        return $this->transactions()->create([
            'type' => $type,
            'count' => $amount,
            'description' => $description
        ]);
    }

    // Accessors

    public function getTotalIncrementsAttribute()
    {
        return $this->transactions()->where('type', 'increment')->sum('count');
    }

    public function getTotalDecrementsAttribute()
    {
        return $this->transactions()->where('type', 'decrement')->sum('count');
    }

}
