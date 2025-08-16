<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\Specialty;
use Modules\Product\Models\SpecialtyItem;
use Modules\Category\Models\Category;

class SpecialtyController extends Controller
{
    public function index(Request $request)
    {
        $query = Specialty::with(['items', 'categories']);

        // Filter: search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Optional: filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->input('category_id'));
            });
        }

        $specialties = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('product::admin.specialty.index', compact('specialties', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('product::admin.specialty.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,select',
            'status' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'items' => 'required_if:type,select|array',
            'items.*' => 'required|string|max:255',
        ]);

        $specialty = Specialty::create([
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status ?? true,
        ]);

        // Attach categories
        if ($request->has('categories')) {
            $specialty->categories()->attach($request->categories);
        }

        // Create items if type is select
        if ($request->type === 'select' && $request->has('items')) {
            foreach ($request->items as $item) {
                $specialty->items()->create(['value' => $item]);
            }
        }

        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی‌ یا موفقیت ثبت شد');
    }

    public function show(Specialty $specialty)
    {
        $specialty->load(['items', 'categories']);
        return view('product::admin.specialty.show', compact('specialty'));
    }

    public function edit(Specialty $specialty)
    {
        $specialty->load(['items', 'categories']);
        $categories = Category::all();
        return view('product::admin.specialty.edit', compact('specialty', 'categories'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,select',
            'status' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'items' => 'required_if:type,select|array',
            'items.*' => 'required|string|max:255',
        ]);

        $specialty->update([
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status ?? true,
        ]);

        // Sync categories
        if ($request->has('categories')) {
            $specialty->categories()->sync($request->categories);
        } else {
            $specialty->categories()->detach();
        }

        // Handle items based on type
        if ($request->type === 'select') {
            // Delete existing items
            $specialty->items()->delete();

            // Create new items
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $specialty->items()->create(['value' => $item]);
                }
            }
        } else {
            // If changed from select to text, delete all items
            $specialty->items()->delete();
        }

        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی‌ یا موفقیت ویرایش شد');
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();
        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی‌ یا موفقیت حذف شد');
    }

    // API methods for getting specialty items
    public function getSpecialtyItems($specialtyId)
    {
        $specialty = Specialty::with('items')->findOrFail($specialtyId);

        if ($specialty->isSelectType()) {
            return response()->json($specialty->items);
        }

        return response()->json(['message' => 'This specialty is text type']);
    }
}
