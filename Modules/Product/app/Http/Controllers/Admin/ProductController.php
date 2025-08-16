<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\ProductStoreRequest;
use Modules\Product\Http\Requests\ProductUpdateRequest;
use Modules\Product\Models\Product;
use Modules\Category\Models\Category;
use Modules\Product\Models\Specialty;
use Modules\Store\Models\Store;

class ProductController extends Controller
{
    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Display paginated products with relationships
     */
    public function index()
    {
        $products = Product::with(['categories', 'specialties', 'store'])
            ->latest()
            ->paginate(15);

        return view('product::admin.product.index', compact('products'));
    }

    /**
     * Show product creation form with required data
     */
    public function create()
    {
        $categories = Category::pluck('name', 'id');
        $specialties = Specialty::active()->pluck('name', 'id');
        $availabilityStatuses = Product::getAvailabilityStatuses();

        return view('product::admin.product.create', compact(
            'categories',
            'specialties',
            'availabilityStatuses'
        ));
    }

    /**
     * Store new product with relationships and initial stock
     */
    public function store(ProductStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        $product = Product::create($data);

        if ($request->filled('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->filled('specialties')) {
            $product->specialties()->attach($request->specialties);
        }

        $this->handleInitialStock($request, $product);

        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت ثبت شد');
    }

    /**
     * Display product details
     */
    public function show(Product $product)
    {
        $product->load(['categories', 'specialties', 'store']);
        return view('product::admin.product.show', compact('product'));
    }

    /**
     * Show product edit form with required data
     */
    public function edit(Product $product)
    {
        $product->load(['categories', 'specialties']);

        $categories = Category::pluck('name', 'id');
        $specialties = Specialty::active()->pluck('name', 'id');
        $availabilityStatuses = Product::getAvailabilityStatuses();

        return view('product::admin.product.edit', compact(
            'product',
            'categories',
            'specialties',
            'availabilityStatuses'
        ));
    }

    /**
     * Update product and sync relationships
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        $product->update($data);

        $request->filled('categories')
            ? $product->categories()->sync($request->categories)
            : $product->categories()->detach();

        $request->filled('specialties')
            ? $product->specialties()->sync($request->specialties)
            : $product->specialties()->detach();

        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت ویرایش شد');
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت حذف شد');
    }

    //======================================================================
    // STATUS MANAGEMENT
    //======================================================================

    /**
     * Toggle product active status
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['status' => !$product->status]);

        $status = $product->status ? 'فعال' : 'غیرفعال';
        return back()->with('success', "وضعیت محصول با موفقیت به {$status} تغییر یافت.");
    }

    /**
     * Update product availability status
     */
    public function changeAvailabilityStatus(Request $request, Product $product)
    {
        $request->validate([
            'availability_status' => 'required|in:coming_soon,available,unavailable'
        ]);

        $product->update([
            'availability_status' => $request->availability_status
        ]);

        return back()->with('success', 'وضعیت موجودی محصول با موفقیت به‌روزرسانی شد.');
    }

    //======================================================================
    // PRIVATE METHODS
    //======================================================================

    /**
     * Handle initial stock creation for new product
     */
    private function handleInitialStock(Request $request, Product $product)
    {
        $initialStock = $request->input('initial_stock', 0);

        if ($initialStock > 0) {
            $store = Store::create([
                'product_id' => $product->id,
                'balance' => $initialStock
            ]);

            $store->transactions()->create([
                'type' => 'increment',
                'count' => $initialStock,
                'description' => 'تعداد اولیه'
            ]);
        }
    }
}
