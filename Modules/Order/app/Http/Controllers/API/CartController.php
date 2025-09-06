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

            $cartItems = Cart::with(['product:id,title,price'])
                ->where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->get();

            $totals = Cart::query()->where('customer_id', $customerId)
                ->selectRaw('COUNT(*) as item_count, SUM(quantity) as total_items, SUM(price * quantity) as total_amount')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'اقلام سبد خرید با موفقیت بازیابی شدند.',
                'data' => [
                    'items' => $cartItems,
                    'summary' => [
                        'item_count' => (int) ($totals->item_count ?? 0),
                        'total_items' => (int) ($totals->total_items ?? 0),
                        'total_amount' => (int) ($totals->total_amount ?? 0),
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

                $product = Product::query()->find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
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

                if ($store->balance < $totalQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'موجودی کافی نیست.',
                        'data' => [
                            'available_stock' => $store->balance,
                            'requested_quantity' => $totalQuantity
                        ]
                    ], 400);
                }

                if ($existingCartItem) {
                    $existingCartItem->quantity += $quantity;
                    $existingCartItem->price = $product->price;
                    $existingCartItem->save();
                    $cartItem = $existingCartItem->load('product:id,title,price');
                    $message = 'مورد سبد خرید با موفقیت به‌روزرسانی شد.';
                } else {
                    $cartItem = Cart::query()->create([
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product->price
                    ]);
                    $cartItem->load('product:id,title,price');
                    $message = 'کالا با موفقیت به سبد خرید اضافه شد.';
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'cart_item' => $cartItem,
                        'total_price' => $cartItem->quantity * $cartItem->price
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

            $cartItem = Cart::with('product:id,title,price')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'کالای سبد خرید با موفقیت بازیابی شد.',
                'data' => $cartItem
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'کالای سبد خرید یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart item',
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

            return DB::transaction(function () use ($customerId, $id, $newQuantity) {

                $cartItem = Cart::with('product')->where('id', $id)->where('customer_id', $customerId)->firstOrFail();

                $store = Store::query()->where('product_id', $cartItem->product_id)->first();
                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'رکورد فروشگاه یافت نشد.'
                    ], 404);
                }

                if ($store->balance < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'موجودی کافی نیست.',
                        'data' => [
                            'available_stock' => $store->balance,
                            'requested_quantity' => $newQuantity
                        ]
                    ], 400);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->price = $cartItem->product->price;
                $cartItem->save();

                $cartItem->load('product:id,title,price');

                return response()->json([
                    'success' => true,
                    'message' => 'تعداد اقلام سبد خرید با موفقیت به‌روزرسانی شد.',
                    'data' => $cartItem
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
                'message' => 'Failed to update cart item',
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

            $cartItem = Cart::query()->where('id', $id)->where('customer_id', $customerId)->firstOrFail();
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'مورد سبد خرید با موفقیت حذف شد.'
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
