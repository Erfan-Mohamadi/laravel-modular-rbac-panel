<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Http\Requests\BrandStoreRequest;
use Modules\Product\Http\Requests\BrandUpdateRequest;
use Modules\Product\Models\Brand;
use Modules\Category\Models\Category;

class BrandController extends Controller
{
    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Display paginated list of brands with their categories
     */
    public function index()
    {
        $brands = Brand::with('categories')->latest('id')->paginate(10);
        return view('product::admin.brand.index', compact('brands'));
    }

    /**
     * Show brand creation form with hierarchical categories
     */
    public function create()
    {
        $categories = Category::root()
            ->with('children.children.children')
            ->get();

        $flattened = [];

        $flattenCategories = function ($categories, $prefix = '') use (&$flattenCategories, &$flattened) {
            foreach ($categories as $category) {
                $flattened[$category->id] = $prefix . $category->name;
                if ($category->children->count()) {
                    $flattenCategories($category->children, $prefix . '— ');
                }
            }
        };

        $flattenCategories($categories);

        return view('product::admin.brand.create', [
            'categories' => $flattened,
        ]);
    }

    /**
     * Store new brand with image and category associations
     */
    public function store(BrandStoreRequest $request)
    {
        $data = [
            'name' => $request->name,
            'status' => $request->has('status'),
            'description' => $request->description
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت ثبت شد');
    }

    /**
     * Show brand details
     */
    public function show(Brand $brand)
    {
        $brand->load('categories');
        return view('product::brands.show', compact('brand'));
    }

    /**
     * Show brand edit form with hierarchical categories
     */
    public function edit(Brand $brand)
    {
        $categories = Category::with('children')->get();

        $flatCategories = [];

        $addCategories = function ($categories, $prefix = '') use (&$flatCategories, &$addCategories) {
            foreach ($categories as $category) {
                $flatCategories[$category->id] = $prefix . $category->name;
                if ($category->children->count()) {
                    $addCategories($category->children, $prefix . '— ');
                }
            }
        };

        $addCategories($categories->where('parent_id', null));

        return view('product::admin.brand.edit', compact('brand', 'flatCategories'));
    }

    /**
     * Update brand including image and categories
     */
    public function update(BrandUpdateRequest $request, Brand $brand)
    {
        $data = [
            'name' => $request->name,
            'status' => $request->has('status'),
            'description' => $request->description
        ];

        if ($request->hasFile('image')) {
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت ویرایش شد');
    }

    /**
     * Delete brand with associated image and category relations
     */
    public function destroy(Brand $brand)
    {
        if ($brand->image) {
            Storage::disk('public')->delete($brand->image);
        }

        $brand->categories()->detach();
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت حذف شد');
    }
}
