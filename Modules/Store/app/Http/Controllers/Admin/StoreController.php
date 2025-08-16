<?php

namespace Modules\Store\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Store\Models\Store;
use Modules\Product\Models\Product;
use Modules\Store\Models\StoreTransaction;

class StoreController extends Controller
{
    public function index()
    {
        $transactions = StoreTransaction::with('store.product')
            ->latest('created_at')
            ->paginate(15);

        $products = Product::with('store')->latest('id')->get();

        return view('store::admin.store.index', compact('transactions', 'products'));
    }



    public function create()
    {
        $products = Product::all();
        return view('store::admin.store.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'balance' => 'required|integer'
        ]);

        Store::create($request->all());

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function edit(Store $store)
    {
        $products = Product::all();
        return view('store::admin.store.edit', compact('store', 'products'));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'balance' => 'required|integer'
        ]);

        $store->update($request->all());

        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function show(Store $store)
    {
        $store->load('transactions');
        return view('store::admin.store.show', compact('store'));
    }

    public function transaction(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|min:1',
            'type' => 'required|in:increase,decrease',
            'description' => 'nullable|string|max:255',
        ]);

        // Find or create store for product
        $store = Store::firstOrCreate(
            ['product_id' => $request->product_id],
            ['balance' => 0]
        );

        $amount = $request->amount;
        $description = $request->description;

        if ($request->type === 'increase') {
            // Increment stock
            $store->increment('balance', $amount);

            $store->transactions()->create([
                'type' => 'increment',
                'count' => $amount,
                'description' => $description,
            ]);
        } else {
            // Decrement stock but prevent negative balance
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
