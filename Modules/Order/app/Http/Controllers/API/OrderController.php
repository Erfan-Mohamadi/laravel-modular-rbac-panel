<?php

namespace Modules\Order\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Order\Models\Cart;
use Modules\Order\Http\Requests\OrderStoreRequest;
use Modules\Order\Http\Requests\OrderUpdateRequest;
use Modules\Store\Models\Store;
use Modules\Customer\Models\Address;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    /**
     * Get all orders for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $orders = Order::with([
                'orderItems.product:id,title,price',
                'address.province'
            ])
                ->where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $orders->getCollection()->transform(function ($order) {
                $shippingCost = $order->calculateShippingCost();
                return [
                    'order' => $order,
                    'total_items' => $order->total_items,
                    'summary' => [
                        'subtotal' => $order->amount,
                        'shipping_cost' => $shippingCost,
                        'total' => $order->amount + $shippingCost
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'سفارش‌ها با موفقیت بازیابی شدند.',
                'data' => $orders
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'بازیابی سفارش‌ها ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new order from cart items.
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $customerId = $request->user()->id;
            $shippingId = $validated['shipping_id'];
            $addressId = $validated['address_id'];

            $address = Address::query()->findOrFail($addressId);

            return DB::transaction(function () use ($customerId, $shippingId, $address) {

                $cartItems = Cart::with('product')
                    ->where('customer_id', $customerId)
                    ->get();

                if ($cartItems->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هیچ کالایی در سبد خرید یافت نشد.'
                    ], 400);
                }
                $totalproduct = 0;
                $totalAmount = 0;
                $orderItemsData = [];

                foreach ($cartItems as $cartItem) {
                    $store = Store::query()->where('product_id', $cartItem->product_id)->first();
                    if (!$store) {
                        return response()->json([
                            'success' => false,
                            'message' => 'رکورد فروشگاه برای محصول ' . $cartItem->product->title . ' یافت نشد.'
                        ], 404);
                    }

                    if ($store->balance < $cartItem->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'موجودی کافی برای محصول ' . $cartItem->product->title . ' موجود نیست.',
                            'data' => [
                                'product' => $cartItem->product->title,
                                'available_stock' => $store->balance,
                                'requested_quantity' => $cartItem->quantity
                            ]
                        ], 400);
                    }

                    $itemTotal = $cartItem->product->price * $cartItem->quantity;
                    $totalAmount += $itemTotal;

                    $orderItemsData[] = [
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'store' => $store,
                        'cart_item' => $cartItem
                    ];
                }

                // Get shipping cost from province_shipping
                $provinceShipping = DB::table('province_shipping')
                    ->where('province_id', $address->province_id)
                    ->where('shipping_id', $shippingId)
                    ->first();

                $shippingCost = $provinceShipping ? $provinceShipping->price : 0;
                $totalproduct += $totalAmount;
                $totalAmount += $shippingCost;
                $order = Order::query()->create([
                    'customer_id' => $customerId,
                    'shipping_id' => $shippingId,
                    'address_id' => $address->id,
                    'amount' => $totalAmount,
                    'shipping_cost' => $shippingCost,
                    'status' => 'new'
                ]);

                foreach ($orderItemsData as $itemData) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price']
                    ]);

                    $itemData['store']->decrement('balance', $itemData['quantity']);
                    $itemData['cart_item']->delete();
                }

                $order->load([
                    'orderItems.product:id,title,price',
                    'address.province'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'سفارش با موفقیت ایجاد شد.',
                    'data' => [
                        'order' => $order,
                        'total_items' => $order->total_items,
                        'summary' => [
                            'subtotal' => $totalproduct,
                            'shipping_cost' => $shippingCost,
                            'total' => $totalAmount
                        ]
                    ]
                ], 201);
            });

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'آدرس یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ایجاد سفارش ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show a specific order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $order = Order::with(['orderItems.product:id,title,price', 'address.province'])
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();


            // Calculate shipping cost safely
            $shippingCost = 0;
            if ($order->address && $order->shipping_id) {
                $provinceShipping = DB::table('province_shipping')
                    ->where('province_id', $order->address->province_id)
                    ->where('shipping_id', $order->shipping_id)
                    ->first();
                $shippingCost = $provinceShipping ? $provinceShipping->price : 0;
            }

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت بازیابی شد.',
                'data' => [
                    'order' => $order,
                    'total_items' => $order->total_items,
                    'summary' => [
                        'subtotal' => $order->amount - $shippingCost,
                        'shipping_cost' => $shippingCost,
                        'total' => $order->amount
                    ]
                ]
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'بازیابی سفارش ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * Update order status (mainly for admin or cancel by customer)
     */
    public function update(OrderUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $customerId = $request->user()->id;
            $newStatus = $validated['status'];

            $order = Order::where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            if (in_array($order->status, ['delivered', 'in_progress']) && $newStatus === 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان لغو این سفارش وجود ندارد.'
                ], 400);
            }

            return DB::transaction(function () use ($order, $newStatus) {
                $order->status = $newStatus;
                $order->save();

                if ($newStatus === 'failed') {
                    foreach ($order->orderItems as $orderItem) {
                        $store = Store::where('product_id', $orderItem->product_id)->first();
                        if ($store) {
                            $store->increment('balance', $orderItem->quantity);
                        }
                    }
                }

                $order->load([
                    'orderItems.product:id,title,price',
                    'address.province'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'وضعیت سفارش با موفقیت به‌روزرسانی شد.',
                    'data' => $order
                ], 200);
            });

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'به‌روزرسانی سفارش ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cancel an order (soft delete or status change)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $order = Order::where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان لغو این سفارش وجود ندارد.'
                ], 400);
            }

            return DB::transaction(function () use ($order) {
                $order->status = 'failed';
                $order->save();

                foreach ($order->orderItems as $orderItem) {
                    $store = Store::where('product_id', $orderItem->product_id)->first();
                    if ($store) {
                        $store->increment('balance', $orderItem->quantity);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'سفارش با موفقیت لغو شد.'
                ], 200);
            });

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'لغو سفارش ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
