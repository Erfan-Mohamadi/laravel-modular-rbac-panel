<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    /**
     * Get the customer that owns the wallet.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the wallet transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Add money to wallet with transaction record.
     */
    public function deposit(int $amount, string $description = null, string $referenceType = null, int $referenceId = null): WalletTransaction
    {
        $transaction = $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);

        $this->increment('balance', $amount);

        return $transaction;
    }

    /**
     * Withdraw money from wallet with transaction record.
     */
    public function withdraw(int $amount, string $description = null, string $referenceType = null, int $referenceId = null): WalletTransaction
    {
        if ($this->balance < $amount) {
            throw new \Exception('موجودی کیف پول کافی نیست.');
        }

        $transaction = $this->transactions()->create([
            'type' => 'withdraw',
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);

        $this->decrement('balance', $amount);

        return $transaction;
    }

    /**
     * Add refund to wallet with transaction record.
     */
    public function refund(int $amount, string $description = null, string $referenceType = null, int $referenceId = null): WalletTransaction
    {
        $transaction = $this->transactions()->create([
            'type' => 'refund',
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);

        $this->increment('balance', $amount);

        return $transaction;
    }

    /**
     * Get wallet balance in human readable format.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance) . ' تومان';
    }
}
