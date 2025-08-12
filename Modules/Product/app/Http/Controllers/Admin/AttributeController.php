<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\AttributeRequest;
use Modules\Product\Models\Attribute;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
    public function index(Request $request)
    {
        $query = Attribute::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $attributes = $query->latest('id')->paginate(15);

        return view('product::admin.attribute.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        return view('product::admin.attribute.create');
    }

    /**
     * Store a newly created attribute (with its items)
     */
    public function store(AttributeRequest $request)
    {
        $data = $request->validated();

        $attribute = Attribute::create($data);

        // Save attribute items if "items" field exists and type is select
        if ($attribute->type === 'select' && $request->filled('items')) {
            $values = array_filter(array_map('trim', explode("\n", $request->input('items'))));
            foreach ($values as $value) {
                if (!empty($value)) {
                    // Prevent duplicate values for this attribute (if unique constraint not enough)
                    if (!$attribute->attributeItems()->where('value', $value)->exists()) {
                        $attribute->attributeItems()->create(['value' => $value]);
                    }
                }
            }
        }

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified attribute
     */
    public function show(Attribute $attribute)
    {
        return view('product::admin.attribute.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute
     */
    public function edit(Attribute $attribute)
    {
        return view('product::admin.attribute.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute
     */
    public function update(AttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute
     */
    public function destroy(Attribute $attribute)
    {
        // You might want to check if attribute is being used by products
        // if ($attribute->products()->exists()) {
        //     return back()->with('error', 'Cannot delete attribute that is being used by products.');
        // }

        $attribute->delete();

        return redirect()
            ->route('attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Toggle attribute status
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
     * Store multiple items at once (for bulk creation)
     */
    public function storeMultiple(Request $request, Attribute $attribute)
    {
        $request->validate([
            'values' => 'required|string',
        ]);

        $values = array_filter(array_map('trim', explode("\n", $request->values)));
        $created = 0;

        foreach ($values as $value) {
            if (!empty($value)) {
                // Check if value already exists
                if (!$attribute->attributeItems()->where('value', $value)->exists()) {
                    $attribute->attributeItems()->create(['value' => $value]);
                    $created++;
                }
            }
        }

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', "{$created} مقدار جدید با موفقیت ایجاد شد.");
    }
}
