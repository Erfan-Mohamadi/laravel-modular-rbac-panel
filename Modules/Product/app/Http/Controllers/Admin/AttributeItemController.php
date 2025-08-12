<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Models\AttributeItem;
use Modules\Product\Http\Requests\AttributeItemRequest;
use Modules\Product\Models\Attribute;

class AttributeItemController extends Controller
{
    /**
     * Display attribute items for a specific attribute
     */
    public function index(Attribute $attribute)
    {
        // Only allow for select type attributes
        if ($attribute->type !== 'select') {
            return redirect()->route('attributes.index')
                ->with('error', 'مقادیر فقط برای خصوصیات انتخابی قابل مدیریت است.');
        }

        $items = $attribute->attributeItems()->latest()->paginate(15);

        return view('product::admin.attribute.items.index', compact('attribute', 'items'));
    }

    /**
     * Show the form for creating a new attribute item
     */
    public function create(Attribute $attribute)
    {
        if ($attribute->type !== 'select') {
            return redirect()->route('attributes.index')
                ->with('error', 'مقادیر فقط برای خصوصیات انتخابی قابل ایجاد است.');
        }

        return view('product::admin.attribute.items.create', compact('attribute'));
    }

    /**
     * Store a newly created attribute item
     */
    public function store(AttributeItemRequest $request, Attribute $attribute)
    {
        $attribute->attributeItems()->create($request->validated());

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified attribute item
     */
    public function edit(Attribute $attribute, AttributeItem $item)
    {
        // Make sure the item belongs to this attribute
        if ($item->attribute_id !== $attribute->id) {
            return redirect()->route('attributes.items.index', $attribute)
                ->with('error', 'مقدار مورد نظر یافت نشد.');
        }

        return view('product::admin.attribute.items.edit', compact('attribute', 'item'));
    }

    /**
     * Update the specified attribute item
     */
    public function update(AttributeItemRequest $request, Attribute $attribute, AttributeItem $item)
    {
        // Make sure the item belongs to this attribute
        if ($item->attribute_id !== $attribute->id) {
            return redirect()->route('attributes.items.index', $attribute)
                ->with('error', 'مقدار مورد نظر یافت نشد.');
        }

        $item->update($request->validated());

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت به‌روزرسانی شد.');
    }

    /**
     * Remove the specified attribute item
     */
    public function destroy(Attribute $attribute, AttributeItem $item)
    {
        // Make sure the item belongs to this attribute
        if ($item->attribute_id !== $attribute->id) {
            return redirect()->route('attributes.items.index', $attribute)
                ->with('error', 'مقدار مورد نظر یافت نشد.');
        }

        $item->delete();

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت حذف شد.');
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
