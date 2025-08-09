<?php

namespace Modules\Area\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Modules\Area\Models\City;
use Modules\Area\Models\Province;
use Modules\Area\Http\Requests\Admin\StoreCityRequest;
use Modules\Area\Http\Requests\Admin\UpdateCityRequest;

class CityController extends Controller
{
    public function index()
    {
        $cities = Cache::rememberForever('cities_list', function () {
            return City::query()->select('id', 'name', 'province_id')
                ->with(['province:id,name'])
                ->latest('id')
                ->paginate(15);
        });

        $provinces = Cache::rememberForever('provinces_list', function () {
            return Province::query()->orderBy('name')->get(['id', 'name']);
        });

        return view('area::admin.areas.index', compact('cities', 'provinces'));
    }

    public function create()
    {
        $provinces = Cache::rememberForever('provinces_list', function () {
            return Province::query()->orderBy('name')->get(['id', 'name']);
        });

        return view('area::admin.areas.create', compact('provinces'));
    }

    public function store(StoreCityRequest $request)
    {
        City::query()->create($request->only('name', 'province_id'));

        Cache::forget('cities_list');

        return redirect()->route('admin.areas.index')->with('success', 'City created successfully.');
    }

    public function edit($id)
    {
        $city = City::query()->findOrFail($id);

        $provinces = Cache::rememberForever('provinces_list', function () {
            return Province::query()->orderBy('name')->get(['id', 'name']);
        });

        return view('area::admin.areas.edit', compact('city', 'provinces'));
    }

    public function update(UpdateCityRequest $request, $id)
    {
        $city = City::query()->findOrFail($id);
        $city->update($request->only('name', 'province_id'));

        Cache::forget('cities_list');

        return redirect()->route('admin.areas.index')->with('success', 'City updated successfully.');
    }

    public function destroy($id)
    {
        City::query()->findOrFail($id)->delete();

        Cache::forget('cities_list');

        return redirect()->route('admin.areas.index')->with('success', 'City deleted successfully.');
    }
}
