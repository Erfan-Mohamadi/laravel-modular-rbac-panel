<?php

namespace Modules\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\Admin;
use Modules\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::query()->with('role')->latest()->get();
        return view('admin::admin.admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $roles = Role::cachedRoles();
        return view('admin::admin.admin.create', compact('roles'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|unique:admins,mobile|max:20',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        Admin::create($validated);

        return redirect()->route('admin.index')->with('success', 'ادمین با موفقیت اضافه شد.');
    }


    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        return view('admin::admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        return view('admin::admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:admins,email,{$admin->id}",
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $admin->update($validated);

        return redirect()->route('admin.admins.index')->with('success', 'ادمین با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'ادمین با موفقیت حذف شد.');
    }

    /**
     * Show permissions page for an admin (if using Spatie).
     */
    public function permissions(Admin $admin)
    {
        return view('admin::admins.permissions', compact('admin'));
    }
}
