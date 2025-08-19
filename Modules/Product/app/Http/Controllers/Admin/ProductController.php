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
        $products = Product::with(['categories', 'specialties', 'store', 'media'])
            ->latest()
            ->paginate(15);

        // Add image URLs to each product for easier access in views
        $products->getCollection()->transform(function ($product) {
            $product->main_image_thumb = $product->getMainImageUrl('thumb');
            $product->gallery_count = $product->getGalleryImagesCount();
            return $product;
        });

        return view('product::admin.product.index', compact('products'));
    }

    /**
     * Show product creation form with required data
     */
    public function create()
    {
        // Get all categories
        $categories = Category::pluck('name', 'id');

        // Get all active specialties with id, name, type
        $specialties = Specialty::active()->get(['id', 'name', 'type']);

        // Get the pivot table mapping (category_id => specialty_ids)
        $categorySpecialty = \DB::table('category_specialty')
            ->select('category_id', 'specialty_id')
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) {
                return $items->pluck('specialty_id')->toArray();
            });

        // Get availability statuses
        $availabilityStatuses = Product::getAvailabilityStatuses();

        // Pass data to the view
        return view('product::admin.product.create', compact(
            'categories',
            'specialties',
            'availabilityStatuses',
            'categorySpecialty'
        ));
    }



    /**
     * Store new product with relationships, images, and initial stock
     */
    public function store(ProductStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        $product = Product::create($data);

        // Handle relationships
        if ($request->filled('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->filled('specialties')) {
            $product->specialties()->attach($request->specialties);
        }

        // Handle image uploads
        $this->handleImageUploads($request, $product);

        // Handle initial stock
        $this->handleInitialStock($request, $product);

        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت ثبت شد');
    }

    /**
     * Display product details
     */
    public function show(Product $product)
    {
        $product->load(['categories', 'specialties', 'store', 'media']);

        return view('product::admin.product.show', compact('product'));
    }

    /**
     * Show product edit form with required data
     */
    public function edit(Product $product)
    {
        $product->load(['categories', 'specialties', 'media']);

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

        // Handle relationships
        $request->filled('categories')
            ? $product->categories()->sync($request->categories)
            : $product->categories()->detach();

        $request->filled('specialties')
            ? $product->specialties()->sync($request->specialties)
            : $product->specialties()->detach();

        // Handle image uploads
        $this->handleImageUploads($request, $product, true);

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
    // IMAGE MANAGEMENT
    //======================================================================

    /**
     * Remove main image
     */
    public function removeMainImage(Product $product)
    {
        $product->clearMediaCollection('main_image');

        return back()->with('success', 'تصویر اصلی محصول با موفقیت حذف شد.');
    }

    /**
     * Remove gallery image
     */
    public function removeGalleryImage(Product $product, $mediaId)
    {
        $media = $product->getMedia('gallery')->where('id', $mediaId)->first();

        if (!$media) {
            return back()->with('error', 'تصویر مورد نظر یافت نشد.');
        }

        $media->delete();

        return back()->with('success', 'تصویر گالری با موفقیت حذف شد.');
    }

    /**
     * Add gallery images via AJAX
     */
    public function addGalleryImages(Request $request, Product $product)
    {
        $request->validate([
            'gallery_images' => 'required|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,webp,jpg|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('gallery_images') as $file) {
            $media = $product->addMedia($file)
                ->toMediaCollection('gallery');

            $uploadedImages[] = [
                'id' => $media->id,
                'name' => $media->name,
                'thumb_url' => $media->getUrl('thumb'),
                'original_url' => $media->getUrl(),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'تصاویر با موفقیت اضافه شدند.',
            'images' => $uploadedImages
        ]);
    }

    //======================================================================
    // PRIVATE METHODS
    //======================================================================

    /**
     * Handle image uploads for both create and update
     */
    private function handleImageUploads(Request $request, Product $product, bool $isUpdate = false)
    {
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            if ($isUpdate) {
                // Clear existing main image when updating
                $product->clearMediaCollection('main_image');
            }

            $product->addMediaFromRequest('main_image')
                ->toMediaCollection('main_image');
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $product->addMedia($file)
                    ->toMediaCollection('gallery');
            }
        }

        // Handle gallery image removal (only for updates)
        if ($isUpdate && $request->has('remove_gallery_images')) {
            foreach ($request->remove_gallery_images as $mediaId) {
                $media = $product->getMedia('gallery')->where('id', $mediaId)->first();
                if ($media) {
                    $media->delete();
                }
            }
        }
    }

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

    public function getSpecialtiesByCategories(Request $request)
    {
        $categoryIds = $request->input('categories', []);

        if (empty($categoryIds)) {
            return response()->json([]);
        }

        $specialties = Specialty::whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        })
            ->where('status', true) // optional: only active specialties
            ->get(['id', 'name']);

        return response()->json($specialties);
    }


}
