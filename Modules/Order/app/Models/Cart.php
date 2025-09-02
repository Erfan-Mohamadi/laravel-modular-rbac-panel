<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Product;

class Cart extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'cart';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'customer_id',
        'quantity',
        'price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'product_id' => 'integer',
        'customer_id' => 'integer',
    ];

    /**
     * Get the product that belongs to the cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer that owns the cart item.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The total_price is a virtual column in the database.
     * No need for accessor since it's calculated by the database.
     *
     * Note: The total_price column is defined as a virtual column:
     * $table->unsignedBigInteger('total_price')->virtualAs('price * quantity');
     */

    /**
     * Scope to get cart items for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
