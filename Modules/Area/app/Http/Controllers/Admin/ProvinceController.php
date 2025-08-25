<?php

namespace Modules\Area\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Area\Models\Province;

class ProvinceController extends Controller
{
    public function index()
    {
        $provinces = Province::query()
            ->withCount('cities')
            ->orderBy('name')
            ->paginate(15);

        return view('area::admin.provinces.index', compact('provinces'));
    }
}
