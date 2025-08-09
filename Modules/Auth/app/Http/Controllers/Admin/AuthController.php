<?php

namespace Modules\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Modules\Admin\Models\Admin;  // Import Admin model

class AuthController extends Controller
{
    // =============================================
    // AUTHENTICATION VIEW METHODS
    // =============================================

    public function showLoginForm(): View
    {
        return view('auth::admin.login');
    }

    // =============================================
    // LOGIN/LOGOUT ACTIONS
    // =============================================

    public function login(Request $request): RedirectResponse
    {
        // Validate login credentials
        $credentials = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Attempt authentication using admin guard
        if (Auth::guard('admin')->attempt(
            ['mobile' => $credentials['mobile'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();

            // Update last login date
            $admin->last_login_date = now();
            $admin->save();

            // Optional login event
            activity('احراز هویت')
                ->causedBy($admin)
                ->withProperties(['موبایل' => $admin->mobile])
                ->log('ادمین وارد حساب شد');

            return redirect()->intended('/admin');
        }

        // Failed authentication
        return back()->withErrors([
            'mobile' => 'اطلاعات وارد شده اشتباه است!',
        ])->onlyInput('mobile');
    }

    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            activity('احراز هویت')
                ->causedBy($admin)
                ->withProperties(['موبایل' => $admin->mobile])
                ->log('ادمین از حساب خارج شد');
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('showLoginForm');
    }
}
