<?php

namespace Modules\Customer\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Http\Requests\AddressStoreRequest;
use Modules\Customer\Http\Requests\AddressUpdateRequest;
use Modules\Customer\Models\Address;

class AddressController extends Controller
{
    // Get all addresses for authenticated customer
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->with(['province', 'city'])->get();

        return response()->json([
            'message' => 'آدرس‌ها با موفقیت بازیابی شدند.',
            'addresses' => $addresses
        ]);
    }

    // Create new address
    public function store(AddressStoreRequest $request)
    {
        $cityBelongsToProvince = \DB::table('cities')
            ->where('id', $request->city_id)
            ->where('province_id', $request->province_id)
            ->exists();

        if (!$cityBelongsToProvince) {
            return response()->json([
                'message' => 'شهر انتخاب شده متعلق به استان انتخاب شده نیست.'
            ], 422);
        }

        $address = $request->user()->addresses()->create($request->validated());

        $address->load(['province', 'city']);

        return response()->json([
            'message' => 'آدرس با موفقیت ایجاد شد.',
            'address' => $address
        ], 201);
    }

    // Get specific address
    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->with(['province', 'city'])->findOrFail($id);

        return response()->json([
            'message' => 'آدرس با موفقیت بازیابی شد.',
            'address' => $address
        ]);
    }

    // Update address
    public function update(AddressUpdateRequest $request, $id)
    {
        $cityBelongsToProvince = \DB::table('cities')
            ->where('id', $request->city_id)
            ->where('province_id', $request->province_id)
            ->exists();

        if (!$cityBelongsToProvince) {
            return response()->json([
                'message' => 'شهر انتخاب شده متعلق به استان انتخاب شده نیست.'
            ], 422);
        }

        $address = $request->user()->addresses()->findOrFail($id);
        $address->update($request->validated());

        $address->load(['province', 'city']);

        return response()->json([
            'message' => 'آدرس با موفقیت به‌روزرسانی شد.',
            'address' => $address
        ]);
    }

    // Delete address
    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'آدرس با موفقیت حذف شد.'
        ]);
    }
}
