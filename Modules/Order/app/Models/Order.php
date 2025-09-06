<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'shipping_cost',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the shipping information for the order.
     */
    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }


    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the total items count.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    /**
     * Check if order can be cancelled by the customer.
     */
    public function canBeCancelled(): bool
    {
        // Only new or waiting for payment orders can be cancelled
        return in_array($this->status, ['new', 'wait_for_payment']);
    }


    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if order has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function calculateShippingCost()
    {
        $provinceId = $this->address?->province_id;

        if (!$provinceId && $this->address_id) {
            $address = Address::find($this->address_id);
            $provinceId = $address?->province_id;
        }

        if (!$provinceId) return 0;

        $provinceShipping = DB::table('province_shipping')
            ->where('province_id', $provinceId)
            ->where('shipping_id', $this->shipping_id)
            ->first();

        return $provinceShipping ? $provinceShipping->price : 0;
    }

}
