<?php

namespace Modules\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\Admin;
use Modules\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Modules\Admin\Http\Requests\Admin\StoreAdminRequest;
use Modules\Admin\Http\Requests\Admin\UpdateAdminRequest;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Cache::rememberForever('admins_list', function () {
            return Admin::with('role')->latest('id')->get();
        });

        return view('admin::admin.admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $roles = Cache::rememberForever('roles_except_super_admin', function () {
            return Role::cachedRoles()->filter(fn($role) => $role->name !== Role::SUPER_ADMIN);
        });

        return view('admin::admin.admin.create', compact('roles'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(StoreAdminRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = bcrypt($validated['password']);

        Admin::query()->create($validated);

        Cache::forget('admins_list');

        return redirect()->route('admin.index')->with('success', 'ادمین با موفقیت اضافه شد.');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        $roles = Cache::rememberForever('roles_except_super_admin', function () {
            return Role::cachedRoles()->filter(fn($role) => $role->name !== Role::SUPER_ADMIN);
        });

        $isSuperAdmin = $admin->role?->name === Role::SUPER_ADMIN;

        return view('admin::admin.admin.edit', compact('roles', 'admin', 'isSuperAdmin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $validated = $request->validated();


        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($admin->role?->name === Role::SUPER_ADMIN) {
            unset($validated['role_id'], $validated['status']);
        }

        $admin->update($validated);

        Cache::forget('admins_list');

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

        Cache::forget('admins_list');

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
     * Show permissions page for an admin.
     */
    public function permissions(Admin $admin)
    {
        return view('admin::admins.permissions', compact('admin'));
    }
}
