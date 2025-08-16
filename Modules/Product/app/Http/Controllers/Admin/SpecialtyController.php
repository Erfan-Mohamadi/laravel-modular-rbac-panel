<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\SpecialtyStoreRequest;
use Modules\Product\Http\Requests\SpecialtyUpdateRequest;
use Modules\Product\Models\Specialty;
use Modules\Product\Models\SpecialtyItem;
use Modules\Category\Models\Category;

class SpecialtyController extends Controller
{
    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Display paginated specialties with filtering options
     */
    public function index(Request $request)
    {
        $query = Specialty::with(['items', 'categories']);

        // Apply filters
        $this->applyFilters($query, $request);

        $specialties = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('product::admin.specialty.index', compact('specialties', 'categories'));
    }

    /**
     * Show specialty creation form
     */
    public function create()
    {
        $categories = Category::all();
        return view('product::admin.specialty.create', compact('categories'));
    }

    /**
     * Store new specialty with categories and items
     */
    public function store(SpecialtyStoreRequest $request)
    {
        $specialty = Specialty::create([
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status ?? true,
        ]);

        $this->syncCategories($specialty, $request);
        $this->handleSpecialtyItems($specialty, $request);

        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی با موفقیت ثبت شد');
    }

    /**
     * Display specialty details
     */
    public function show(Specialty $specialty)
    {
        $specialty->load(['items', 'categories']);
        return view('product::admin.specialty.show', compact('specialty'));
    }

    /**
     * Show specialty edit form
     */
    public function edit(Specialty $specialty)
    {
        $specialty->load(['items', 'categories']);
        $categories = Category::all();
        return view('product::admin.specialty.edit', compact('specialty', 'categories'));
    }

    /**
     * Update specialty with categories and items
     */
    public function update(SpecialtyUpdateRequest $request, Specialty $specialty)
    {
        $specialty->update([
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status ?? true,
        ]);

        $this->syncCategories($specialty, $request);
        $this->handleSpecialtyItems($specialty, $request);

        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی با موفقیت ویرایش شد');
    }

    /**
     * Delete specialty
     */
    public function destroy(Specialty $specialty)
    {
        $specialty->delete();
        return redirect()->route('specialties.index')
            ->with('success', 'ویژگی با موفقیت حذف شد');
    }

    //======================================================================
    // API ENDPOINTS
    //======================================================================

    /**
     * Get specialty items for API response
     */
    public function getSpecialtyItems($specialtyId)
    {
        $specialty = Specialty::with('items')->findOrFail($specialtyId);

        if ($specialty->isSelectType()) {
            return response()->json($specialty->items);
        }

        return response()->json(['message' => 'This specialty is text type']);
    }

    //======================================================================
    // PRIVATE METHODS
    //======================================================================

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->input('search')}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->input('category_id'));
            });
        }
    }

    /**
     * Sync specialty categories
     */
    private function syncCategories(Specialty $specialty, Request $request)
    {
        $request->filled('categories')
            ? $specialty->categories()->sync($request->categories)
            : $specialty->categories()->detach();
    }

    /**
     * Handle specialty items based on type
     */
    private function handleSpecialtyItems(Specialty $specialty, Request $request)
    {
        $specialty->items()->delete();

        if ($request->type === 'select' && $request->filled('items')) {
            foreach ($request->items as $item) {
                $specialty->items()->create(['value' => $item]);
            }
        }
    }
}
