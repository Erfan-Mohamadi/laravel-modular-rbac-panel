<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the reference model (polymorphic relationship).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get transaction type label.
     */
    public function getTypeLabel(): string
    {
        return [
            'deposit' => 'واریز',
            'withdraw' => 'برداشت',
            'refund' => 'بازگشت وجه',
        ][$this->type] ?? $this->type;
    }

    /**
     * Get formatted amount with sign.
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = in_array($this->type, ['deposit', 'refund']) ? '+' : '-';
        return $sign . number_format($this->amount) . ' تومان';
    }

    /**
     * Scope for specific transaction types.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for deposits and refunds.
     */
    public function scopeIncoming($query)
    {
        return $query->whereIn('type', ['deposit', 'refund']);
    }

    /**
     * Scope for withdrawals.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', 'withdraw');
    }
}
