<?php

namespace Modules\Shipping\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Shipping\Models\Shipping;
use Modules\Area\Models\Province;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippings = Shipping::withCount('provinces')->latest('id')->paginate(10);
        return view('shipping::admin.shipping.index', compact('shippings'));
    }

    /**
     * Show the form for creating a new shipping.
     */
    public function create()
    {
        $provinces = Province::all();
        return view('shipping::admin.shipping.create', compact('provinces'));
    }

    /**
     * Store a newly created shipping in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shipping,name',
            'status' => 'boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048',
            'provinces.*.selected' => 'nullable|boolean',
            'provinces.*.price' => 'nullable|required_with:provinces.*.selected|numeric|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $data['image'] = $request->file('icon')->store('shippings', 'public');
        }

        $shipping = Shipping::create($data);

        // Attach provinces with prices
        $attachData = [];
        if ($request->has('provinces')) {
            foreach ($request->provinces as $provinceId => $provinceData) {
                if (!empty($provinceData['selected'])) {
                    $attachData[$provinceId] = [
                        'price' => isset($provinceData['price']) ? (int) $provinceData['price'] : 0,
                    ];
                }
            }
        }
        $shipping->provinces()->attach($attachData);

        return redirect()->route('shipping.index')
            ->with('success', 'حمل و نقل جدید ثبت شد.');
    }

    /**
     * Show the form for editing the specified shipping.
     */
    public function edit(Shipping $shipping)
    {
        $provinces = Province::all();
        $shipping->load('provinces');

        return view('shipping::admin.shipping.edit', compact('shipping', 'provinces'));
    }

    /**
     * Update the specified shipping in storage.
     */
    public function update(Request $request, Shipping $shipping)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shipping,name,' . $shipping->id,
            'status' => 'boolean',
            'icon' => 'nullable|image|mimes:png,jpeg,jpg,gif|max:2048',
            'provinces.*.selected' => 'nullable|boolean',
            'provinces.*.price' => 'nullable|required_with:provinces.*.selected|numeric|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            if ($shipping->image) {
                Storage::disk('public')->delete($shipping->image);
            }
            $data['image'] = $request->file('icon')->store('shippings', 'public');
        }

        $shipping->update($data);

        // Sync provinces with prices
        $syncData = [];
        if ($request->has('provinces')) {
            foreach ($request->provinces as $provinceId => $provinceData) {
                if (!empty($provinceData['selected'])) {
                    $syncData[$provinceId] = [
                        'price' => isset($provinceData['price']) ? (int) $provinceData['price'] : 0,
                    ];
                }
            }
        }
        $shipping->provinces()->sync($syncData);

        return redirect()->route('shipping.index')
            ->with('success', 'حمل و نقل ویرایش شد.');
    }

    /**
     * Remove the specified shipping from storage.
     */
    public function destroy(Shipping $shipping)
    {
        if ($shipping->image) {
            Storage::disk('public')->delete($shipping->image);
        }

        $shipping->delete();

        return redirect()->route('shipping.index')
            ->with('success', 'حمل و نقل حذف شد.');
    }
}
