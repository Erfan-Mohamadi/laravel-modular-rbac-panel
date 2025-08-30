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
        $provinces = Province::all();

        return response()->json([
            'message' => 'Provinces retrieved successfully',
            'provinces' => $provinces
        ]);
    }

    // Get cities by province
    public function cities(Request $request, $provinceId)
    {
        $request->validate([
            'province_id' => 'exists:provinces,id'
        ]);

        $cities = City::where('province_id', $provinceId)->get();

        return response()->json([
            'message' => 'Cities retrieved successfully',
            'cities' => $cities
        ]);
    }

    // Get all cities (optional)
    public function allCities()
    {
        $cities = City::with('province')->get();

        return response()->json([
            'message' => 'All cities retrieved successfully',
            'cities' => $cities
        ]);
    }
}
