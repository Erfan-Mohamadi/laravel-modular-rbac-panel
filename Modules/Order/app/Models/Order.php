<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Models\Address;
use Modules\Customer\Models\Customer;
use Modules\Shipping\Models\Shipping;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shipping_id',
        'address_id',
        'formatted_address',
        'shipping_cost',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
        'shipping_cost' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    // Keep for admin reference only
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the invoice for this order.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    public function getSubtotalAttribute(): int
    {
        return $this->amount - $this->shipping_cost;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['new', 'wait_for_payment']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'delivered';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if order has been paid.
     */
    public function isPaid(): bool
    {
        return $this->invoice && $this->invoice->isPaid();
    }

    /**
     * Update order status based on payment status.
     */
    public function updateStatusBasedOnPayment(): void
    {
        if ($this->isPaid() && $this->status === 'wait_for_payment') {
            $this->update(['status' => 'in_progress']);
        }
    }

    /**
     * Create formatted address string from Address model
     */
    public static function formatAddress(Address $address): string
    {
        // Make sure province and city are loaded
        $address->load(['province', 'city']);

        $parts = array_filter([
            $address->title ? "({$address->title})" : null,
            $address->province->name ?? '',
            $address->city->name ?? '',
            $address->district,
            $address->address_line,
            $address->postal_code ? "کد پستی: {$address->postal_code}" : null
        ]);

        return implode('، ', $parts);
    }

    public function calculateShippingCost(): int
    {
        if ($this->shipping_cost !== null && $this->shipping_cost > 0) {
            return $this->shipping_cost;
        }

        $provinceId = null;

        if ($this->relationLoaded('address') && $this->address) {
            $provinceId = $this->address->province_id;
        } elseif ($this->address_id) {
            $address = Address::find($this->address_id);
            $provinceId = $address?->province_id;
        }

        if (!$provinceId || !$this->shipping_id) {
            return 0;
        }

        $provinceShipping = DB::table('province_shipping')
            ->where('province_id', $provinceId)
            ->where('shipping_id', $this->shipping_id)
            ->first();

        return $provinceShipping ? (int) $provinceShipping->price : 0;
    }
}
