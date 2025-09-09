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
                'orderItems.product:id,title,price',
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
                $cartItems = Cart::with('product')
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
                    $subtotal += $itemTotal;

                    $orderItemsData[] = [
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'store' => $store,
                        'cart_item' => $cartItem
                    ];
                }

                // Calculate shipping cost
                $provinceShipping = DB::table('province_shipping')
                    ->where('province_id', $address->province_id)
                    ->where('shipping_id', $shippingId)
                    ->first();

                $shippingCost = $provinceShipping ? (int) $provinceShipping->price : 0;
                $totalAmount = $subtotal + $shippingCost;

                // Create order with formatted address snapshot
                $order = Order::query()->create([
                    'customer_id' => $customerId,
                    'shipping_id' => $shippingId,
                    'address_id' => $address->id,
                    'formatted_address' => Order::formatAddress($address),
                    'amount' => $totalAmount,
                    'shipping_cost' => $shippingCost,
                    'status' => 'wait_for_payment'
                ]);

                // Create order items and update inventory
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

                // Create invoice for the order
                $invoice = Invoice::query()->create([
                    'order_id' => $order->id,
                    'amount' => $totalAmount,
                    'status' => 'pending'
                ]);

                $paymentResult = $this->paymentService->processPayment($invoice);

                $order->load([
                    'orderItems.product:id,title,price',
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
                        'summary' => [
                            'subtotal' => $subtotal,
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

            $order = Order::with([
                'orderItems.product:id,title,price',
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
                    'payment_status' => $order->invoice ? $this->paymentService->getPaymentStatus($order->invoice) : null,
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

            $order->load([
                'orderItems.product:id,title,price',
                'invoice.latestPayment'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت دریافت شد.',
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
                'message' => 'عملیات ناموفق بود.',
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
                'orderItems.product:id,title,price',
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
                $order->status = 'failed';
                $order->save();

                if ($order->invoice) {
                    $order->invoice->markAsFailed();
                }

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
}
