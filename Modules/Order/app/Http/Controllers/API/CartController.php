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

            $totals = Cart::where('customer_id', $customerId)
                ->selectRaw('COUNT(*) as item_count, SUM(quantity) as total_items, SUM(price * quantity) as total_amount')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Cart items retrieved successfully',
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
                'message' => 'Failed to retrieve cart items',
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

                $product = Product::find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }

                $store = Store::where('product_id', $productId)->first();
                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Store record not found for this product'
                    ], 404);
                }

                $existingCartItem = Cart::where('customer_id', $customerId)
                    ->where('product_id', $productId)
                    ->first();

                $totalQuantity = $existingCartItem ? $existingCartItem->quantity + $quantity : $quantity;

                if ($store->balance < $totalQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock available',
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
                    $message = 'Cart item updated successfully';
                } else {
                    $cartItem = Cart::create([
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product->price
                    ]);
                    $cartItem->load('product:id,title,price');
                    $message = 'Item added to cart successfully';
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
                'message' => 'Failed to add item to cart',
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
                'message' => 'Cart item retrieved successfully',
                'data' => $cartItem
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
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

                $store = Store::where('product_id', $cartItem->product_id)->first();
                if (!$store) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Store record not found'
                    ], 404);
                }

                if ($store->balance < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock available',
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
                    'message' => 'Cart item quantity updated successfully',
                    'data' => $cartItem
                ], 200);
            });

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
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

            $cartItem = Cart::where('id', $id)->where('customer_id', $customerId)->firstOrFail();
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully'
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear all cart items for the authenticated customer.
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;
            $deletedCount = Cart::where('customer_id', $customerId)->delete();

            return response()->json([
                'success' => true,
                'message' => $deletedCount > 0
                    ? "Cart cleared successfully. {$deletedCount} items removed."
                    : "Cart is already empty.",
                'deleted_items_count' => $deletedCount
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get cart summary.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $summary = Cart::where('customer_id', $customerId)
                ->selectRaw('COUNT(*) as item_count, SUM(quantity) as total_items, SUM(price * quantity) as total_amount')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Cart summary retrieved successfully',
                'data' => [
                    'item_count' => (int) ($summary->item_count ?? 0),
                    'total_items' => (int) ($summary->total_items ?? 0),
                    'total_amount' => (int) ($summary->total_amount ?? 0),
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart summary',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get cart item count.
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;
            $count = Cart::where('customer_id', $customerId)->sum('quantity');

            return response()->json([
                'success' => true,
                'message' => 'Cart count retrieved successfully',
                'data' => ['count' => (int) $count]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart count',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Sync cart: remove unavailable items and update prices.
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            return DB::transaction(function () use ($customerId) {
                $cartItems = Cart::with('product')->where('customer_id', $customerId)->get();
                $removedItems = [];
                $updatedItems = [];

                foreach ($cartItems as $cartItem) {
                    $store = Store::where('product_id', $cartItem->product_id)->first();
                    if (!$cartItem->product || !$store || $store->balance <= 0) {
                        $removedItems[] = ['id' => $cartItem->id, 'reason' => 'Product unavailable'];
                        $cartItem->delete();
                        continue;
                    }

                    if ($cartItem->quantity > $store->balance) {
                        $oldQty = $cartItem->quantity;
                        $cartItem->quantity = $store->balance;
                        $cartItem->save();
                        $updatedItems[] = ['id' => $cartItem->id, 'old_quantity' => $oldQty, 'new_quantity' => $store->balance];
                    }

                    if ($cartItem->price !== $cartItem->product->price) {
                        $cartItem->price = $cartItem->product->price;
                        $cartItem->save();
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Cart synchronized successfully',
                    'data' => [
                        'removed_items' => $removedItems,
                        'updated_items' => $updatedItems,
                        'removed_count' => count($removedItems),
                        'updated_count' => count($updatedItems)
                    ]
                ], 200);
            });

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cart',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk update cart items.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:carts,id',
            'items.*.quantity' => 'required|integer|min:1|max:999'
        ]);

        try {
            $customerId = $request->user()->id;
            $items = $request->input('items');

            return DB::transaction(function () use ($customerId, $items) {
                $updatedItems = [];
                $errors = [];

                foreach ($items as $item) {
                    try {
                        $cartItem = Cart::with('product')->where('id', $item['id'])->where('customer_id', $customerId)->firstOrFail();
                        $store = Store::where('product_id', $cartItem->product_id)->first();

                        if (!$store || $store->balance < $item['quantity']) {
                            $errors[] = ['id' => $item['id'], 'message' => 'Insufficient stock', 'available_stock' => $store->balance ?? 0];
                            continue;
                        }

                        $cartItem->quantity = $item['quantity'];
                        $cartItem->price = $cartItem->product->price;
                        $cartItem->save();
                        $updatedItems[] = $cartItem->load('product:id,title,price');

                    } catch (ModelNotFoundException) {
                        $errors[] = ['id' => $item['id'], 'message' => 'Cart item not found'];
                    }
                }

                return response()->json([
                    'success' => count($errors) === 0,
                    'message' => count($errors) === 0 ? 'All items updated successfully' : 'Some items could not be updated',
                    'data' => [
                        'updated_items' => $updatedItems,
                        'errors' => $errors,
                        'updated_count' => count($updatedItems),
                        'error_count' => count($errors)
                    ]
                ], count($errors) === 0 ? 200 : 422);
            });

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart items',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
