<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\AttributeItemStoreRequest;
use Modules\Product\Http\Requests\AttributeItemUpdateRequest;
use Modules\Product\Models\AttributeItem;
use Modules\Product\Models\Attribute;

class AttributeItemController extends Controller
{
    //======================================================================
    // CRUD OPERATIONS
    //======================================================================

    /**
     * Display paginated items for a select-type attribute
     */
    public function index(Attribute $attribute)
    {
        if ($attribute->type !== 'select') {
            return redirect()->route('attributes.index')
                ->with('error', 'مقادیر فقط برای خصوصیات انتخابی قابل مدیریت است.');
        }

        $items = $attribute->attributeItems()->latest()->paginate(15);
        return view('product::admin.attribute.items.index', compact('attribute', 'items'));
    }

    /**
     * Show form for creating new attribute item
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
     * Store new attribute item
     */
    public function store(AttributeItemStoreRequest $request, Attribute $attribute)
    {
        $attribute->attributeItems()->create($request->validated());

        return redirect()->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت ایجاد شد.');
    }

    /**
     * Show form for editing attribute item
     */
    public function edit(Attribute $attribute, AttributeItem $item)
    {
        if ($item->attribute_id !== $attribute->id) {
            return redirect()->route('attributes.items.index', $attribute)
                ->with('error', 'مقدار مورد نظر یافت نشد.');
        }

        return view('product::admin.attribute.items.edit', compact('attribute', 'item'));
    }

    /**
     * Update existing attribute item
     */
    public function update(AttributeItemUpdateRequest $request, Attribute $attribute, AttributeItem $item)
    {
        $item->update($request->validated());

        return redirect()->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت به‌روزرسانی شد.');
    }

    /**
     * Delete attribute item
     */
    public function destroy(Attribute $attribute, AttributeItem $item)
    {
        if ($item->attribute_id !== $attribute->id) {
            return redirect()->route('attributes.items.index', $attribute)
                ->with('error', 'مقدار مورد نظر یافت نشد.');
        }

        $item->delete();

        return redirect()
            ->route('attributes.items.index', $attribute)
            ->with('success', 'مقدار ویژگی با موفقیت حذف شد.');
    }

    //======================================================================
    // BULK OPERATIONS
    //======================================================================

    /**
     * Bulk create attribute items from text input
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
