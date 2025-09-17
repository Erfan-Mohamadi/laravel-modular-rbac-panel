<?php

namespace Modules\Order\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Order\Models\Cart;
use Modules\Order\Models\Invoice;
use Modules\Order\Http\Requests\OrderStoreRequest;
use Modules\Order\Http\Requests\OrderUpdateRequest;
use Modules\Store\Models\Store;
use Modules\Customer\Models\Address;
use Modules\Payment\Services\PaymentService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get all orders for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $orders = Order::with([
                'orderItems.product:id,title,price,weight',
                'invoice.latestPayment'
            ])
                ->where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $orders->getCollection()->transform(function ($order) {
                return [
                    'order' => $order,
                    'shipping_address' => $order->formatted_address,
                    'total_items' => $order->total_items,
                    'total_weight' => $order->total_weight,
                    'payment_status' => $order->invoice ? [
                        'invoice_status' => $order->invoice->status,
                        'payment_info' => $order->invoice->latestPayment ? [
                            'status' => $order->invoice->latestPayment->status,
                            'tracking_code' => $order->invoice->latestPayment->tracing_code,
                            'message' => $order->invoice->latestPayment->message,
                        ] : null
                    ] : null,
                    'summary' => [
                        'subtotal' => $order->subtotal,
                        'shipping_cost' => $order->shipping_cost,
                        'total' => $order->amount
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

            // Load address with relationships
            $address = Address::with(['province', 'city'])->findOrFail($addressId);

            return DB::transaction(function () use ($customerId, $shippingId, $address) {
                // Load cart items with product weight information
                $cartItems = Cart::with(['product:id,title,price,weight'])
                    ->where('customer_id', $customerId)
                    ->get();

                if ($cartItems->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هیچ کالایی در سبد خرید یافت نشد.'
                    ], 400);
                }

                $subtotal = 0;
                $orderItemsData = [];

                // Validate and prepare order items
                foreach ($cartItems as $cartItem) {
                    $product = $cartItem->product;

                    $store = Store::query()->where('product_id', $cartItem->product_id)->first();
                    if (!$store) {
                        return response()->json([
                            'success' => false,
                            'message' => 'رکورد فروشگاه برای محصول ' . $product->title . ' یافت نشد.'
                        ], 404);
                    }

                    if ($store->balance < $cartItem->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'موجودی کافی برای محصول ' . $product->title . ' موجود نیست.',
                            'data' => [
                                'product' => $product->title,
                                'available_stock' => $store->balance,
                                'requested_quantity' => $cartItem->quantity
                            ]
                        ], 400);
                    }

                    // Validate product weight
                    if (!$product->weight || $product->weight <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'وزن محصول ' . $product->title . ' تعریف نشده است.'
                        ], 400);
                    }

                    $itemTotal = $product->price * $cartItem->quantity;
                    $subtotal += $itemTotal;

                    $orderItemsData[] = [
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $product->price,
                        'weight' => $product->weight,
                        'store' => $store,
                        'cart_item' => $cartItem
                    ];
                }

                // Calculate weight-based shipping cost
                $shippingCalculation = $this->calculateWeightBasedShipping($orderItemsData, $address->province_id, $shippingId);
                $shippingCost = $shippingCalculation['cost'];
                $totalAmount = $subtotal + $shippingCost;

                // Create order with formatted address snapshot
                $order = Order::query()->create([
                    'customer_id' => $customerId,
                    'shipping_id' => $shippingId,
                    'address_id' => $address->id,
                    'formatted_address' => Order::formatAddress($address),
                    'amount' => $totalAmount,
                    'shipping_cost' => $shippingCost,
                    'total_weight' => $shippingCalculation['total_weight'],
                    'status' => 'wait_for_payment'
                ]);

                // Create order items and update inventory
                foreach ($orderItemsData as $itemData) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'weight' => $itemData['weight']
                    ]);

                    $itemData['store']->decrement('balance', $itemData['quantity']);
                    $itemData['cart_item']->delete();
                }

                // Create invoice for the order
                $invoice = Invoice::query()->create([
                    'order_id' => $order->id,
                    'amount' => $totalAmount,
                    'status' => 'pending'
                ]);

                $paymentResult = $this->paymentService->processPayment($invoice);

                $order->load([
                    'orderItems.product:id,title,price,weight',
                    'invoice.latestPayment'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'سفارش با موفقیت ایجاد شد.',
                    'data' => [
                        'order' => $order,
                        'shipping_address' => $order->formatted_address,
                        'total_items' => $order->total_items,
                        'payment_result' => $paymentResult,
                        'invoice' => [
                            'id' => $invoice->id,
                            'status' => $invoice->status,
                            'amount' => $invoice->amount
                        ],
                        'weight_info' => $shippingCalculation['breakdown'],
                        'summary' => [
                            'subtotal' => $subtotal,
                            'shipping_cost' => $shippingCost,
                            'total_weight_grams' => $shippingCalculation['total_weight'],
                            'shipping_units_100g' => $shippingCalculation['shipping_units'],
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

            $order = Order::with([
                'orderItems.product:id,title,price,weight',
                'invoice.latestPayment'
            ])
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت بازیابی شد.',
                'data' => [
                    'order' => $order,
                    'shipping_address' => $order->formatted_address,
                    'total_items' => $order->total_items,
                    'total_weight' => $order->total_weight ?? $this->calculateOrderWeight($order),
                    'payment_status' => $order->invoice ? $this->paymentService->getPaymentStatus($order->invoice) : null,
                    'weight_breakdown' => $this->getOrderWeightBreakdown($order),
                    'summary' => [
                        'subtotal' => $order->subtotal,
                        'shipping_cost' => $order->shipping_cost,
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
     * Update order status
     */
    public function update(OrderUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $customerId = $request->user()->id;

            $order = Order::query()->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            // Only allow certain status updates by customer
            $allowedStatuses = ['cancelled']; // Add more if needed
            if (isset($validated['status']) && in_array($validated['status'], $allowedStatuses)) {
                $order->update(['status' => $validated['status']]);
            }

            $order->load([
                'orderItems.product:id,title,price,weight',
                'invoice.latestPayment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت به‌روزرسانی شد.',
                'data' => $order
            ], 200);

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
     * Retry payment for an order
     */
    public function retryPayment(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $order = Order::with('invoice')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            if (!$order->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'فاکتور برای این سفارش یافت نشد.'
                ], 404);
            }

            if ($order->invoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این سفارش قبلاً پرداخت شده است.'
                ], 400);
            }

            if (!in_array($order->status, ['wait_for_payment', 'failed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان پرداخت مجدد برای این سفارش وجود ندارد.'
                ], 400);
            }

            // Update order status to wait for payment
            $order->update(['status' => 'wait_for_payment']);

            // Reset invoice status to pending
            $order->invoice->update(['status' => 'pending']);

            // Process payment
            $paymentResult = $this->paymentService->processPayment($order->invoice);

            $order->refresh();
            $order->load([
                'orderItems.product:id,title,price,weight',
                'invoice.latestPayment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'درخواست پرداخت مجدد با موفقیت پردازش شد.',
                'data' => [
                    'order' => $order,
                    'payment_result' => $paymentResult,
                    'payment_status' => $this->paymentService->getPaymentStatus($order->invoice)
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
                'message' => 'پرداخت مجدد ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $order = Order::with('invoice')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان لغو این سفارش وجود ندارد.'
                ], 400);
            }

            if ($order->invoice && $order->invoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان لغو سفارش پرداخت شده وجود ندارد.'
                ], 400);
            }

            return DB::transaction(function () use ($order) {
                $order->status = 'cancelled';
                $order->save();

                if ($order->invoice) {
                    $order->invoice->markAsFailed();
                }

                // Restore inventory
                foreach ($order->orderItems as $orderItem) {
                    $store = Store::query()->where('product_id', $orderItem->product_id)->first();
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

    /**
     * Get payment status for an order
     */
    public function paymentStatus(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $order = Order::with('invoice.latestPayment')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            if (!$order->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'فاکتور برای این سفارش یافت نشد.'
                ], 404);
            }

            $paymentStatus = $this->paymentService->getPaymentStatus($order->invoice);

            return response()->json([
                'success' => true,
                'message' => 'وضعیت پرداخت با موفقیت دریافت شد.',
                'data' => [
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice->id,
                    'invoice_status' => $order->invoice->status,
                    'payment_status' => $paymentStatus
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
                'message' => 'دریافت وضعیت پرداخت ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate shipping estimate before placing order
     */
    public function calculateShippingEstimate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'address_id' => 'required|integer|exists:addresses,id',
                'shipping_id' => 'required|integer'
            ]);

            $customerId = $request->user()->id;
            $addressId = $request->input('address_id');
            $shippingId = $request->input('shipping_id');

            // Load address
            $address = Address::with(['province', 'city'])->findOrFail($addressId);

            // Load cart items
            $cartItems = Cart::with(['product:id,title,price,weight'])
                ->where('customer_id', $customerId)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'هیچ کالایی در سبد خرید یافت نشد.'
                ], 400);
            }

            // Prepare order items data for calculation
            $orderItemsData = [];
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                if (!$product->weight || $product->weight <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'وزن محصول ' . $product->title . ' تعریف نشده است.'
                    ], 400);
                }

                $orderItemsData[] = [
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                    'weight' => $product->weight
                ];
            }

            // Calculate shipping
            $shippingCalculation = $this->calculateWeightBasedShipping($orderItemsData, $address->province_id, $shippingId);

            return response()->json([
                'success' => true,
                'message' => 'هزینه حمل و نقل محاسبه شد.',
                'data' => [
                    'shipping_cost' => $shippingCalculation['cost'],
                    'total_weight_grams' => $shippingCalculation['total_weight'],
                    'shipping_units_100g' => $shippingCalculation['shipping_units'],
                    'weight_breakdown' => $shippingCalculation['breakdown']
                ]
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'آدرس یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'محاسبه هزینه حمل و نقل ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate shipping cost based on total weight
     */
    private function calculateWeightBasedShipping(array $orderItemsData, int $provinceId, int $shippingId): array
    {
        // Get base shipping rate per 100g for this province and shipping method
        $provinceShipping = DB::table('province_shipping')
            ->where('province_id', $provinceId)
            ->where('shipping_id', $shippingId)
            ->first();

        if (!$provinceShipping) {
            return [
                'cost' => 0,
                'total_weight' => 0,
                'shipping_units' => 0,
                'breakdown' => []
            ];
        }

        $basePricePer100g = (int) $provinceShipping->price;

        // Calculate total weight and create breakdown
        $totalWeightInGrams = 0;
        $breakdown = [];

        foreach ($orderItemsData as $item) {
            $itemWeight = $item['weight']; // Weight per unit in grams
            $itemTotalWeight = $itemWeight * $item['quantity'];
            $totalWeightInGrams += $itemTotalWeight;

            $breakdown[] = [
                'product_id' => $item['product_id'],
                'weight_per_unit_grams' => $itemWeight,
                'quantity' => $item['quantity'],
                'total_weight_grams' => $itemTotalWeight
            ];
        }

        // Convert to 100g units and round up
        $weight100gUnits = ceil($totalWeightInGrams / 100);

        // Calculate final shipping cost
        $shippingCost = $weight100gUnits * $basePricePer100g;

        return [
            'cost' => $shippingCost,
            'total_weight' => $totalWeightInGrams,
            'shipping_units' => $weight100gUnits,
            'rate_per_100g' => $basePricePer100g,
            'breakdown' => $breakdown
        ];
    }

    /**
     * Calculate total weight for an existing order
     */
    private function calculateOrderWeight(Order $order): int
    {
        $totalWeight = 0;

        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->weight && $orderItem->weight > 0) {
                $totalWeight += $orderItem->weight * $orderItem->quantity;
            } elseif ($orderItem->product && $orderItem->product->weight > 0) {
                $totalWeight += $orderItem->product->weight * $orderItem->quantity;
            }
        }

        return $totalWeight;
    }

    /**
     * Get weight breakdown for an existing order
     */
    private function getOrderWeightBreakdown(Order $order): array
    {
        $breakdown = [];
        $totalWeight = 0;

        foreach ($order->orderItems as $orderItem) {
            $itemWeight = $orderItem->weight ?? ($orderItem->product->weight ?? 0);
            $itemTotalWeight = $itemWeight * $orderItem->quantity;
            $totalWeight += $itemTotalWeight;

            $breakdown[] = [
                'product_id' => $orderItem->product_id,
                'product_title' => $orderItem->product->title ?? 'Unknown',
                'weight_per_unit_grams' => $itemWeight,
                'quantity' => $orderItem->quantity,
                'total_weight_grams' => $itemTotalWeight
            ];
        }

        return [
            'items' => $breakdown,
            'total_weight_grams' => $totalWeight,
            'total_weight_100g_units' => ceil($totalWeight / 100)
        ];
    }
}
