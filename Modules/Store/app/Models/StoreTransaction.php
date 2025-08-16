<?php
// Modules/Store/Models/StoreTransaction.php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class StoreTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'type',
        'count',
        'description'
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    // Transaction types constants
    const TYPE_INCREMENT = 'increment';
    const TYPE_DECREMENT = 'decrement';

    // Get all transaction types
    public static function getTransactionTypes()
    {
        return [
            self::TYPE_INCREMENT => 'Increment',
            self::TYPE_DECREMENT => 'Decrement'
        ];
    }

    // Relationship with Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Scopes
    public function scopeIncrements(Builder $query)
    {
        return $query->where('type', self::TYPE_INCREMENT);
    }

    public function scopeDecrements(Builder $query)
    {
        return $query->where('type', self::TYPE_DECREMENT);
    }

    public function scopeByType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForStore(Builder $query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeRecent(Builder $query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedCountAttribute()
    {
        return number_format($this->count, 2);
    }

    public function getTypeLabelAttribute()
    {
        return self::getTransactionTypes()[$this->type] ?? 'Unknown';
    }

    public function getSignedCountAttribute()
    {
        return $this->type === self::TYPE_INCREMENT ?
            '+' . $this->formatted_count :
            '-' . $this->formatted_count;
    }

    // Helper Methods
    public function isIncrement()
    {
        return $this->type === self::TYPE_INCREMENT;
    }

    public function isDecrement()
    {
        return $this->type === self::TYPE_DECREMENT;
    }

    protected static function newFactory()
    {
        return \Modules\Store\Database\factories\StoreTransactionFactory::new();
    }
}
