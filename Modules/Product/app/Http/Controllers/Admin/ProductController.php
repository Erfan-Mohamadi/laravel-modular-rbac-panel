<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Product\Models\Specialty;
use Modules\Store\Models\Store;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['categories', 'specialties', 'store'])
            ->latest()
            ->paginate(15);
        return view('product::admin.product.index', compact('products'));
    }

    public function create()
    {
        // Return as key-value arrays for select2 dropdowns
        $categories = Category::pluck('name', 'id');
        $specialties = Specialty::active()->pluck('name', 'id');
        $availabilityStatuses = Product::getAvailabilityStatuses();

        return view('product::admin.product.create', compact('categories', 'specialties', 'availabilityStatuses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|lt:price',
            'availability_status' => 'required|in:coming_soon,available,unavailable',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'specialties' => 'array',
            'specialties.*' => 'exists:specialties,id',
            'initial_stock' => 'nullable|integer|min:0' // ✅ Add this
        ]);

        $data = $request->all();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        $product = Product::create($data);

        // Attach categories
        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        // Attach specialties
        if ($request->has('specialties')) {
            $product->specialties()->attach($request->specialties);
        }

        // ✅ Create initial stock in stores
        $initialStock = $request->input('initial_stock', 0);
        if ($initialStock > 0) {
            $store = Store::create([
                'product_id' => $product->id,
                'balance' => $initialStock
            ]);

            // Add a store transaction for initial stock
            $store->transactions()->create([
                'type' => 'increment',
                'count' => $initialStock,
                'description' => 'تعداد اولیه'
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت ثبت شد');
    }
    public function show(Product $product)
    {
        $product->load(['categories', 'specialties', 'store']);
        return view('product::admin.product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['categories', 'specialties']);


        // Return as key-value arrays for select2 dropdowns
        $categories = Category::pluck('name', 'id');
        $specialties = Specialty::active()->pluck('name', 'id');
        $availabilityStatuses = Product::getAvailabilityStatuses();

        return view('product::admin.product.edit', compact('product', 'categories',
            'specialties', 'availabilityStatuses'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0|lt:price',
            'availability_status' => 'required|in:coming_soon,available,unavailable',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'specialties' => 'array',
            'specialties.*' => 'exists:specialties,id',
        ]);


        $data = $request->all();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        $product->update($data);

        // Sync categories
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        } else {
            $product->categories()->detach();
        }

        // Sync specialties
        if ($request->has('specialties')) {
            $product->specialties()->sync($request->specialties);
        } else {
            $product->specialties()->detach();
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    // Toggle product status
    public function toggleStatus(Product $product)
    {
        $product->update([
            'status' => !$product->status
        ]);

        $status = $product->status ? 'activated' : 'deactivated';
        return back()->with('success', "Product {$status} successfully.");
    }

    // Change availability status
    public function changeAvailabilityStatus(Request $request, Product $product)
    {
        $request->validate([
            'availability_status' => 'required|in:coming_soon,available,unavailable'
        ]);

        $product->update([
            'availability_status' => $request->availability_status
        ]);

        return back()->with('success', 'Product availability status updated successfully.');
    }
}
