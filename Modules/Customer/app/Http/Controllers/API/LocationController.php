<?php

namespace Modules\Customer\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Area\Models\City;
use Modules\Area\Models\Province;

class LocationController extends Controller
{
    // Get all provinces
    public function provinces()
    {
        $provinces = Province::query()->select('id', 'name')->get();

        return response()->json([
            'message' => 'استان‌ها با موفقیت بازیابی شدند.',
            'provinces' => $provinces
        ]);
    }

    // Get cities by province
    public function cities(Request $request, $provinceId)
    {
        $request->validate([
            'province_id' => 'exists:provinces,id'
        ]);

        $cities = City::query()->where('province_id', $provinceId)->get();

        return response()->json([
            'message' => 'شهرها با موفقیت بازیابی شدند.',
            'cities' => $cities
        ]);
    }

    // Get all cities (optional)
    public function allCities()
    {
        $cities = City::with('province')->get();

        return response()->json([
            'message' => 'همه شهرها با موفقیت بازیابی شدند.',
            'cities' => $cities
        ]);
    }
}
