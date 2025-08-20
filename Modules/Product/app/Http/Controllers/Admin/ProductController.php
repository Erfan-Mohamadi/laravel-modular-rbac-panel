<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\ProductStoreRequest;
use Modules\Product\Http\Requests\ProductUpdateRequest;
use Modules\Product\Models\Brand;
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
        $products = Product::with(['category', 'store', 'media'])
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
            })
            ->toArray(); // Convert to array for JavaScript

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
    /**
     * Store new product with relationships, images, and initial stock
     */
    public function store(ProductStoreRequest $request)
    {
        // 1️⃣ Validate request
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        $data['discount'] = $data['discount'] ?? 0;

        // 2️⃣ Create product
        $product = Product::create($data);

        // 3️⃣ Handle specialty relationships with pivot data
        if ($request->filled('specialties')) {
            $specialtyData = [];

            foreach ($request->specialties as $specialtyId) {
                $specialty = Specialty::find($specialtyId);

                if ($specialty) {
                    if ($specialty->isTextType()) {
                        $customValue = $request->input("specialty_values.{$specialtyId}");
                        $specialtyData[$specialtyId] = [
                            'value' => $customValue,
                            'specialty_item_id' => null
                        ];
                    } elseif ($specialty->isSelectType()) {
                        $selectedItemIds = $request->input("specialty_items.{$specialtyId}", []);
                        if (!is_array($selectedItemIds)) {
                            $selectedItemIds = [$selectedItemIds];
                        }
                        foreach ($selectedItemIds as $itemId) {
                            $specialtyData[$specialtyId] = [
                                'value' => null,
                                'specialty_item_id' => $itemId
                            ];
                        }
                    } else {
                        $specialtyData[$specialtyId] = [
                            'value' => null,
                            'specialty_item_id' => null
                        ];
                    }
                }
            }

            if (!empty($specialtyData)) {
                $product->specialties()->attach($specialtyData);
            }
        }

        // 4️⃣ Handle image uploads
        $this->handleImageUploads($request, $product);

        // 5️⃣ Handle initial stock
        $this->handleInitialStock($request, $product);

        // 6️⃣ Handle brands if using AJAX
        if ($request->filled('brands')) {
            $product->brands()->sync($request->brands);
        }

        return redirect()->route('products.index')
            ->with('success', 'محصول با موفقیت ثبت شد');
    }




    /**
     * Display product details
     */
    public function show(Product $product)
    {
        // Load relationships
        $product->load(['category', 'brands', 'specialties.items', 'store', 'media']);

        // Prepare specialty values
        $specialtyValues = [];
        foreach ($product->specialties as $specialty) {
            $pivot = $specialty->pivot;
            if ($specialty->isTextType()) {
                $specialtyValues[$specialty->id] = $pivot->value;
            } elseif ($specialty->isSelectType() && $pivot->specialty_item_id) {
                $item = $specialty->items->where('id', $pivot->specialty_item_id)->first();
                $specialtyValues[$specialty->id] = $item ? $item->value : null;
            } else {
                $specialtyValues[$specialty->id] = null;
            }
        }

        // Prepare gallery images array for Blade
        $galleryImages = $product->getMedia('gallery')->map(function ($media) {
            return [
                'id' => $media->id,
                'original' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
            ];
        })->toArray(); // ✅ convert to array


        return view('product::admin.product.show', compact('product', 'specialtyValues', 'galleryImages'));
    }



    /**
     * Show product edit form with required data
     */
    public function edit(Product $product)
    {
        // Load all necessary relationships
        $product->load(['specialties.items', 'brands', 'media']);

        $categories = Category::all();
        $specialties = Specialty::with('items')->get();
        $availabilityStatuses = Product::getAvailabilityStatuses();
        $brands = Brand::all();

        // Prepare specialty values for form population
        $specialtyValues = [];
        foreach ($product->specialties as $specialty) {
            if ($specialty->type === 'text') {
                $specialtyValues[$specialty->id] = $specialty->pivot->value;
            } elseif ($specialty->type === 'select') {
                // Handle multiple selected items for select type specialties
                $specialtyValues[$specialty->id] = $specialty->pivot->specialty_item_id
                    ? [$specialty->pivot->specialty_item_id]
                    : [];
            }
        }

        // ✅ fetch gallery images
        $galleryImages = $product->getMedia('gallery');

        return view('product::admin.product.edit', compact(
            'product',
            'categories',
            'specialties',
            'availabilityStatuses',
            'brands',
            'specialtyValues',
            'galleryImages'
        ));
    }



    /**
     * Update product and sync relationships
     */
    /**
     * Update product and sync relationships
     */
// Update product
    public function update(ProductUpdateRequest $request, Product $product)
    {
        // Update product fields
        $product->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'discount' => $request->discount,
            'availability' => $request->availability,
            'category_id' => $request->category_id,
        ]);

        // Handle main image
        if ($request->has('remove_main_image')) {
            $product->clearMediaCollection('main');
        }
        if ($request->hasFile('main_image')) {
            $product->clearMediaCollection('main');
            $product->addMediaFromRequest('main_image')->toMediaCollection('main');
        }

        // Handle gallery images
        if ($request->has('remove_gallery_images')) {
            foreach ($request->remove_gallery_images as $mediaId) {
                $media = $product->media()->find($mediaId);
                if ($media) {
                    $media->delete();
                }
            }
        }
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $product->addMediaFromRequest($image)->toMediaCollection('gallery');
            }
        }

        // Sync specialties and brands (same as before)
        // ... rest of your specialty and brand syncing code

        return redirect()
            ->route('products.index')
            ->with('success', 'محصول با موفقیت ویرایش شد.');
    }


    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        // 1️⃣ Detach specialties
        $product->specialties()->detach();

        // 2️⃣ Detach brands (if any)
        if (method_exists($product, 'brands')) {
            $product->brands()->detach();
        }

        // 3️⃣ Delete media (main image + gallery)
        $product->clearMediaCollection('main_image');
        $product->clearMediaCollection('gallery');

        // 4️⃣ Delete store and transactions
        if ($product->store) {
            $product->store->transactions()->delete();
            $product->store->delete();
        }

        // 5️⃣ Delete the product itself
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

    public function getSpecialtiesByCategory(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        $specialties = Specialty::whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })
            ->active()
            ->with(['items' => function($query) {
                $query->select('id', 'specialty_id', 'value');
            }])
            ->get(['id', 'name', 'type']);

        // Transform the data to include type information and items
        $transformedSpecialties = $specialties->map(function ($specialty) {
            return [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'type' => $specialty->type,
                'type_label' => $specialty->getTypeLabelAttribute(),
                'items' => $specialty->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value
                    ];
                })
            ];
        });

        return response()->json($transformedSpecialties);
    }

    public function brandsByCategory(Request $request)
    {
        $categoryId = $request->query('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        $brands = Brand::whereHas('categories', function($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })->get(['id', 'name']);

        return response()->json($brands);
    }



}
