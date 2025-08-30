<?php

namespace Modules\Customer\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Models\Address;

class AddressController extends Controller
{
    // Get all addresses for authenticated customer
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->with(['province', 'city'])->get();

        return response()->json([
            'message' => 'Addresses retrieved successfully',
            'addresses' => $addresses
        ]);
    }

    // Create new address
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'address_line' => 'nullable|string|max:500',
        ]);

        // Verify city belongs to province
        $cityBelongsToProvince = \DB::table('cities')
            ->where('id', $request->city_id)
            ->where('province_id', $request->province_id)
            ->exists();

        if (!$cityBelongsToProvince) {
            return response()->json([
                'message' => 'Selected city does not belong to the selected province'
            ], 422);
        }

        $address = $request->user()->addresses()->create([
            'title' => $request->title,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district' => $request->district,
            'postal_code' => $request->postal_code,
            'address_line' => $request->address_line,
        ]);

        $address->load(['province', 'city']); // Load relationships

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address
        ], 201);
    }

    // Get specific address
    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->with(['province', 'city'])->findOrFail($id);

        return response()->json([
            'message' => 'Address retrieved successfully',
            'address' => $address
        ]);
    }

    // Update address
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'address_line' => 'nullable|string|max:500',
        ]);

        // Verify city belongs to province
        $cityBelongsToProvince = \DB::table('cities')
            ->where('id', $request->city_id)
            ->where('province_id', $request->province_id)
            ->exists();

        if (!$cityBelongsToProvince) {
            return response()->json([
                'message' => 'Selected city does not belong to the selected province'
            ], 422);
        }

        $address = $request->user()->addresses()->findOrFail($id);

        $address->update([
            'title' => $request->title,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district' => $request->district,
            'postal_code' => $request->postal_code,
            'address_line' => $request->address_line,
        ]);

        $address->load(['province', 'city']); // Load relationships

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address
        ]);
    }

    // Delete address
    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully'
        ]);
    }
}
