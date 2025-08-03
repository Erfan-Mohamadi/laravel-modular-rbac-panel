<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // =============================================
    // DASHBOARD DISPLAY METHODS
    // =============================================

    /**
     * Display admin dashboard with upcoming cheque payments
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard::admin.dashboard');
    }
}
