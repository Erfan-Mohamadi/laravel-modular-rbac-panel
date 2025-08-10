<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Models\Brand;
use Modules\Category\Models\Category;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::with('categories')->latest('id')->paginate(10);
        return view('product::admin.brand.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'status' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        $data = [
            'name' => $request->name,
            'status' => $request->has('status'),
            'description' => $request->description
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        // Sync categories
        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت ثبت شد');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        $brand->load('categories');
        return view('product::brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
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

        $addCategories($categories->where('parent_id', null)); // only root categories to start

        return view('product::admin.brand.edit', compact('brand', 'flatCategories'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'status' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        $data = [
            'name' => $request->name,
            'status' => $request->has('status'),
            'description' => $request->description
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($brand->image) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        // Sync categories
        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت ویرایش شد');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Delete image if exists
        if ($brand->image) {
            Storage::disk('public')->delete($brand->image);
        }

        // Detach categories
        $brand->categories()->detach();

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'برند با موققیت حذف شد');
    }
}
