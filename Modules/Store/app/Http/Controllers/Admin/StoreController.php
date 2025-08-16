<?php

namespace Modules\Store\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Store\Http\Requests\StoreStoreRequest;
use Modules\Store\Http\Requests\StoreTransactionRequest;
use Modules\Store\Http\Requests\StoreUpdateRequest;
use Modules\Store\Models\Store;
use Modules\Product\Models\Product;
use Modules\Store\Models\StoreTransaction;

class StoreController extends Controller
{
    //======================================================================
    // VIEW METHODS
    //======================================================================

    /**
     * Display store inventory and transactions
     */
    public function index()
    {
        $transactions = StoreTransaction::with('store.product')
            ->latest('created_at')
            ->paginate(15);

        $products = Product::with('store')->latest('id')->get();

        return view('store::admin.store.index', compact('transactions', 'products'));
    }

    /**
     * Show form to create new store entry
     */
    public function create()
    {
        $products = Product::all();
        return view('store::admin.store.create', compact('products'));
    }

    /**
     * Show form to edit store entry
     */
    public function edit(Store $store)
    {
        $products = Product::all();
        return view('store::admin.store.edit', compact('store', 'products'));
    }

    /**
     * Show store details with transactions
     */
    public function show(Store $store)
    {
        $store->load('transactions');
        return view('store::admin.store.show', compact('store'));
    }

    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Store new inventory record
     */
    public function store(StoreStoreRequest $request)
    {
        Store::create($request->validated());

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully.');
    }

    /**
     * Update inventory record
     */
    public function update(StoreUpdateRequest $request, Store $store)
    {
        $data = $request->validated();
        $store->update($data);

        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    /**
     * Delete inventory record
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    //======================================================================
    // INVENTORY OPERATIONS
    //======================================================================

    /**
     * Process inventory transaction (increase/decrease stock)
     */
    public function transaction(StoreTransactionRequest $request)
    {
        $store = Store::firstOrCreate(
            ['product_id' => $request->product_id],
            ['balance' => 0]
        );

        $amount = $request->amount;
        $description = $request->description;

        if ($request->type === 'increase') {
            $store->increment('balance', $amount);
            $store->transactions()->create([
                'type' => 'increment',
                'count' => $amount,
                'description' => $description,
            ]);
        } else {
            if ($amount > $store->balance) {
                return redirect()->back()->withErrors(['amount' => 'مقدار بیشتر از موجودی فعلی است.']);
            }
            $store->decrement('balance', $amount);
            $store->transactions()->create([
                'type' => 'decrement',
                'count' => $amount,
                'description' => $description,
            ]);
        }

        return redirect()->route('stores.index')->with('success', 'تراکنش با موفقیت انجام شد.');
    }
}
