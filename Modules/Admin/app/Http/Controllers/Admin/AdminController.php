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
        // Exclude super_admin role from selectable roles
        $roles = Role::cachedRoles()->filter(fn($role) => $role->name !== Role::SUPER_ADMIN);
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
            'status' => 'required|boolean',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $superAdminRoleId = Role::where('name', Role::SUPER_ADMIN)->value('id');

        if ($validated['role_id'] == $superAdminRoleId) {
            return back()->withErrors(['role_id' => 'نمی‌توانید نقش مدیر کل را به این ادمین اختصاص دهید.'])
                ->withInput();
        }

        $validated['password'] = bcrypt($validated['password']);

        Admin::create($validated);

        return redirect()->route('admin.index')->with('success', 'ادمین با موفقیت اضافه شد.');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        // Exclude super_admin role from selectable roles
        $roles = Role::cachedRoles()->filter(fn($role) => $role->name !== Role::SUPER_ADMIN);
        $isSuperAdmin = $admin->role?->name === Role::SUPER_ADMIN;
        return view('admin::admin.admin.edit', compact('roles', 'admin', 'isSuperAdmin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => "required|string|max:20|unique:admins,mobile,{$admin->id}",
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $superAdminRoleId = Role::where('name', Role::SUPER_ADMIN)->value('id');

        if ($validated['role_id'] == $superAdminRoleId) {
            return back()->withErrors(['role_id' => 'نمی‌توانید نقش مدیر کل را به این ادمین اختصاص دهید.'])
                ->withInput();
        }

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Prevent role and status change if this admin is super_admin
        if ($admin->role?->name === Role::SUPER_ADMIN) {
            unset($validated['role_id'], $validated['status']);
        }

        $admin->update($validated);

        return redirect()->route('admin.index')->with('success', 'ادمین با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(Admin $admin)
    {
        if ($admin->role?->name === Role::SUPER_ADMIN) {
            return redirect()->route('admin.index')->withErrors('نمی‌توانید مدیر کل را حذف کنید.');
        }

        $admin->delete();

        return redirect()->route('admin.index')->with('success', 'ادمین با موفقیت حذف شد.');
    }


    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        return view('admin::admins.show', compact('admin'));
    }

    /**
     * Show permissions page for an admin (if using Spatie).
     */
    public function permissions(Admin $admin)
    {
        return view('admin::admins.permissions', compact('admin'));
    }
}
