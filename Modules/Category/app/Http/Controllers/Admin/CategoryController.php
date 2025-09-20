<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Models\Category;
use Modules\Category\Http\Requests\Admin\StoreCategoryRequest;
use Modules\Category\Http\Requests\Admin\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::rememberForever('categories_list', function () {
            return Category::query()->latest('id')->paginate(10);
        });

        return view('category::admin.category.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Cache::rememberForever('categories_parents', function () {
            return Category::active()
                ->root()
                ->with('children.children.children')
                ->get();
        });

        return view('category::admin.category.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->only(['name', 'parent_id']);
        $data['status'] = $request->status == '1';

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        Category::query()->create($data);

        Cache::forget('categories_list');
        Cache::forget('categories_parents');

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی جدید ثبت شد.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Cache::rememberForever('categories_parents', function () {
            return Category::active()
                ->root()
                ->with('children.children.children')
                ->get();
        });

        return view('category::admin.category.edit', compact('parentCategories', 'category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->only(['name', 'parent_id']);
        $data['status'] = $request->status == '1';

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $category->update($data);

        Cache::forget('categories_list');
        Cache::forget('categories_parents');

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی ویرایش شد.');
    }

    public function destroy(Category $category)
    {
        if ($category->brands()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'نمی‌توان دسته را حذف کرد. برندهای مرتبط دارد.');
        }

        $category->delete();

        Cache::forget('categories_list');
        Cache::forget('categories_parents');

        return redirect()->route('categories.index')
            ->with('success', 'دسته بندی حذف شد.');
    }
}
