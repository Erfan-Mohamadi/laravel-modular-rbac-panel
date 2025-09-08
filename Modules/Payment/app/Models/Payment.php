<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'Payments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'amount',
        'transaction_id',
        'tracing_code',
        'status',
        'driver',
        'message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'integer',
        'transaction_id' => 'integer',
        'tracing_code' => 'integer',
        'status' => 'boolean',
        'invoice_id' => 'integer',
    ];

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\Modules\Order\Models\Invoice::class);
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === true;
    }

    /**
     * Check if payment has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === false;
    }

    /**
     * Mark payment as successful.
     */
    public function markAsSuccess(string $tracingCode = null, string $message = 'Payment completed successfully'): void
    {
        $this->update([
            'status' => true,
            'driver' => 'bankpal',
            'tracing_code' => $tracingCode,
            'message' => $message,
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $message = 'Payment failed'): void
    {
        $this->update([
            'status' => false,
            'message' => $message,
        ]);
    }
}
