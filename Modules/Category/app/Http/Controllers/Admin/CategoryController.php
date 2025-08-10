<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::query()->latest('id')->paginate(10);
        return view('category::admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $parentCategories = Category::active()
            ->root()
            ->with('children.children.children')
            ->get();

        return view('category::admin.category.create', compact('parentCategories'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'status' => $request->status == '1'
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی جدید ثبت شد.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('category::show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::active()
            ->root()
            ->with('children.children.children')
            ->get();

        return view('category::admin.category.edit', compact('parentCategories', 'category'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'status' => $request->status == '1'
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی ویرایش شد.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has associated brands
        if ($category->brands()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category. It has associated brands.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی حذف شد.');
    }
}
