<?php

namespace Modules\Order\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Cart;
use Modules\Order\Http\Requests\CartStoreRequest;
use Modules\Order\Http\Requests\CartUpdateRequest;
use Modules\Product\Models\Product;
use Modules\Store\Models\Store;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends Controller
{
    /**
     * Get all cart items for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            // Load cart items with product weight information
            $cartItems = Cart::with(['product:id,title,price,weight'])
                ->where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate totals including weight
            $itemCount = 0;
            $totalItems = 0;
            $totalAmount = 0;
            $totalWeight = 0;

            foreach ($cartItems as $cartItem) {
                $itemCount++;
                $totalItems += $cartItem->quantity;
                $totalAmount += $cartItem->price * $cartItem->quantity;

                if ($cartItem->product && $cartItem->product->weight) {
                    $totalWeight += $cartItem->product->weight * $cartItem->quantity;
                }
            }

            // Transform cart items to include weight information
            $cartItems->transform(function ($cartItem) {
                $itemWeight = $cartItem->product->weight ?? 0;
                $totalItemWeight = $itemWeight * $cartItem->quantity;

                return [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total_price' => $cartItem->price * $cartItem->quantity,
                    'created_at' => $cartItem->created_at,
                    'updated_at' => $cartItem->updated_at,
                    'product' => $cartItem->product,
                    'weight_info' => [
                        'weight_per_unit' => $itemWeight,
                        'total_weight' => $totalItemWeight,
                        'weight_unit' => 'grams'
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'اقلام سبد خرید با موفقیت بازیابی شدند.',
                'data' => [
                    'items' => $cartItems,
                    'summary' => [
                        'item_count' => $itemCount,
                        'total_items' => $totalItems,
                        'total_amount' => $totalAmount,
                        'total_weight_grams' => $totalWeight,
                        'total_weight_100g_units' => ceil($totalWeight / 100)
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'بازیابی اقلام سبد خرید ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Add item to cart or update quantity if it already exists.
     */
    public function store(CartStoreRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $customerId = $request->user()->id;
            $productId = $validated['product_id'];
            $quantity = $validated['quantity'];

            return DB::transaction(function () use ($customerId, $productId, $quantity) {

                // Load product with weight information
                $product = Product::query()->select('id', 'title', 'price', 'weight')->find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'محصول یافت نشد.'
                    ], 404);
                }

                // Validate product weight
                if (!$product->weight || $product->weight <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'وزن محصول تعریف نشده است.',
                        'data' => [
                            'product' => $product->title
                        ]
                    ], 400);
                }

                $store = Store::query()->where('product_id', $productId)->first();
                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'رکورد فروشگاه برای این محصول یافت نشد.'
                    ], 404);
                }

                $existingCartItem = Cart::query()->where('customer_id', $customerId)
                    ->where('product_id', $productId)
                    ->first();

                $totalQuantity = $existingCartItem ? $existingCartItem->quantity + $quantity : $quantity;

                // Check stock availability
                if ($store->balance < $totalQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'موجودی کافی نیست.',
                        'data' => [
                            'product' => $product->title,
                            'available_stock' => $store->balance,
                            'requested_quantity' => $totalQuantity,
                            'current_in_cart' => $existingCartItem ? $existingCartItem->quantity : 0
                        ]
                    ], 400);
                }

                if ($existingCartItem) {
                    // Update existing cart item
                    $existingCartItem->quantity += $quantity;
                    $existingCartItem->price = $product->price; // Update price in case it changed
                    $existingCartItem->save();
                    $cartItem = $existingCartItem->load('product:id,title,price,weight');
                    $message = 'مورد سبد خرید با موفقیت به‌روزرسانی شد.';
                } else {
                    // Create new cart item
                    $cartItem = Cart::query()->create([
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product->price
                    ]);
                    $cartItem->load('product:id,title,price,weight');
                    $message = 'کالا با موفقیت به سبد خرید اضافه شد.';
                }

                $itemWeight = $cartItem->product->weight ?? 0;
                $totalItemWeight = $itemWeight * $cartItem->quantity;

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'cart_item' => $cartItem,
                        'total_price' => $cartItem->quantity * $cartItem->price,
                        'weight_info' => [
                            'weight_per_unit' => $itemWeight,
                            'total_weight' => $totalItemWeight,
                            'weight_unit' => 'grams'
                        ]
                    ]
                ], 201);
            });

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'افزودن کالا به سبد خرید ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a specific cart item.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $cartItem = Cart::with('product:id,title,price,weight')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $itemWeight = $cartItem->product->weight ?? 0;
            $totalItemWeight = $itemWeight * $cartItem->quantity;

            return response()->json([
                'success' => true,
                'message' => 'کالای سبد خرید با موفقیت بازیابی شد.',
                'data' => [
                    'cart_item' => $cartItem,
                    'total_price' => $cartItem->quantity * $cartItem->price,
                    'weight_info' => [
                        'weight_per_unit' => $itemWeight,
                        'total_weight' => $totalItemWeight,
                        'weight_unit' => 'grams'
                    ]
                ]
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'کالای سبد خرید یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'بازیابی کالای سبد خرید ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(CartUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $customerId = $request->user()->id;
            $newQuantity = $validated['quantity'];

            // Validate quantity is positive
            if ($newQuantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد باید بیشتر از صفر باشد.'
                ], 400);
            }

            return DB::transaction(function () use ($customerId, $id, $newQuantity) {

                $cartItem = Cart::with('product:id,title,price,weight')
                    ->where('id', $id)
                    ->where('customer_id', $customerId)
                    ->firstOrFail();

                // Validate product weight
                if (!$cartItem->product->weight || $cartItem->product->weight <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'وزن محصول تعریف نشده است.',
                        'data' => [
                            'product' => $cartItem->product->title
                        ]
                    ], 400);
                }

                $store = Store::query()->where('product_id', $cartItem->product_id)->first();
                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'رکورد فروشگاه یافت نشد.'
                    ], 404);
                }

                // Check stock availability
                if ($store->balance < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'موجودی کافی نیست.',
                        'data' => [
                            'product' => $cartItem->product->title,
                            'available_stock' => $store->balance,
                            'requested_quantity' => $newQuantity,
                            'current_quantity' => $cartItem->quantity
                        ]
                    ], 400);
                }

                // Update cart item
                $cartItem->quantity = $newQuantity;
                $cartItem->price = $cartItem->product->price; // Update price in case it changed
                $cartItem->save();

                $itemWeight = $cartItem->product->weight ?? 0;
                $totalItemWeight = $itemWeight * $cartItem->quantity;

                return response()->json([
                    'success' => true,
                    'message' => 'تعداد اقلام سبد خرید با موفقیت به‌روزرسانی شد.',
                    'data' => [
                        'cart_item' => $cartItem,
                        'total_price' => $cartItem->quantity * $cartItem->price,
                        'weight_info' => [
                            'weight_per_unit' => $itemWeight,
                            'total_weight' => $totalItemWeight,
                            'weight_unit' => 'grams'
                        ]
                    ]
                ], 200);
            });

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'کالای سبد خرید یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'به‌روزرسانی کالای سبد خرید ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $cartItem = Cart::with('product:id,title')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $productTitle = $cartItem->product->title ?? 'Unknown Product';
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'محصول "' . $productTitle . '" با موفقیت از سبد خرید حذف شد.'
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'کالای سبد خرید یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حذف کالا از سبد خرید ناموفق بود.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
