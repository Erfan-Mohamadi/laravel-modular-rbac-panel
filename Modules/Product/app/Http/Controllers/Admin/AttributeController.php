<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\AttributeStoreRequest;
use Modules\Product\Http\Requests\AttributeUpdateRequest;
use Modules\Product\Models\Attribute;

class AttributeController extends Controller
{
    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Display paginated attributes with search and status filtering
     */
    public function index(Request $request)
    {
        $query = Attribute::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $attributes = $query->latest('id')->paginate(15);

        return view('product::admin.attribute.index', compact('attributes'));
    }

    /**
     * Show attribute creation form
     */
    public function create()
    {
        return view('product::admin.attribute.create');
    }

    /**
     * Store new attribute with associated items for select type
     */
    public function store(AttributeStoreRequest $request)
    {
        $data = $request->validated();
        $attribute = Attribute::query()->create($data);

        if ($attribute->type === 'select' && $request->filled('items')) {
            $values = array_filter(array_map('trim', explode("\n", $request->input('items'))));
            foreach ($values as $value) {
                if (!empty($value) && !$attribute->attributeItems()->where('value', $value)->exists()) {
                    $attribute->attributeItems()->create(['value' => $value]);
                }
            }
        }

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display attribute details
     */
    public function show(Attribute $attribute)
    {
        return view('product::admin.attribute.show', compact('attribute'));
    }

    /**
     * Show attribute edit form
     */
    public function edit(Attribute $attribute)
    {
        return view('product::admin.attribute.edit', compact('attribute'));
    }

    /**
     * Update existing attribute
     */
    public function update(AttributeUpdateRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Delete attribute (product usage check available)
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    //======================================================================
    // STATUS & BULK OPERATIONS
    //======================================================================

    /**
     * Toggle attribute active status
     */
    public function toggleStatus(Attribute $attribute)
    {
        $attribute->update([
            'status' => !$attribute->status
        ]);

        $status = $attribute->status ? 'activated' : 'deactivated';

        return redirect()
            ->route('attributes.index')
            ->with('success', "Attribute {$status} successfully.");
    }

    /**
     * Bulk create attribute items from newline-separated values
     */
    public function storeMultiple(Request $request, Attribute $attribute)
    {
        $request->validate([
            'values' => 'required|string',
        ]);

        $values = array_filter(array_map('trim', explode("\n", $request->values)));
        $created = 0;

        foreach ($values as $value) {
            if (!empty($value) && !$attribute->attributeItems()->where('value', $value)->exists()) {
                $attribute->attributeItems()->create(['value' => $value]);
                $created++;
            }
        }

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', "{$created} مقدار جدید با موفقیت ایجاد شد.");
    }
}
