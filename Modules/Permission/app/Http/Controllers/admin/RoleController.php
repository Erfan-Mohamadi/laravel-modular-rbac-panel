<?php

namespace Modules\Permission\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Permission\Http\Requests\Admin\StoreRoleRequest;
use Modules\Permission\Http\Requests\Admin\UpdateRoleRequest;
use Modules\Permission\Models\Permission;
use Modules\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Renderable
    {
        $roles = Role::cachedRoles();
        $superAdminName = Role::SUPER_ADMIN;

        return view('permission::admin.role.index', compact('roles', 'superAdminName'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::cachedPermissions();

        return view('permission::admin.role.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
        ]);

        // Check role name before any potential caching issues
        if (!empty($validated['permissions']) && $validated['name'] !== Role::SUPER_ADMIN) {
            $role->givePermissionTo($validated['permissions']);
        }

        // Clear all related caches
        Role::clearAllCaches();

        return redirect()->route('roles.index')->with('success', 'نقش با موفقیت ثبت شد');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Use fresh query instead of cached to ensure we get current data
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::cachedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $superAdminName = Role::SUPER_ADMIN;

        return view('permission::admin.role.edit', compact('role', 'permissions', 'rolePermissions', 'superAdminName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        // Get fresh role instance (not from cache)
        $role = Role::findOrFail($id);
        $validated = $request->validated();

        // Store the original name before update for comparison
        $originalRoleName = $role->name;

        $role->update([
            'name' => $validated['name'],
            'label' => $validated['label'] ?? null,
        ]);

        // Use original name for comparison, not the potentially cached one
        if ($originalRoleName !== Role::SUPER_ADMIN && $validated['name'] !== Role::SUPER_ADMIN) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        // Clear all related caches after operations
        Role::clearAllCaches();

        return redirect()->route('roles.index')->with('success', 'نقش با موفقیت به‌روزرسانی شد');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Get fresh role instance with users count
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->name === Role::SUPER_ADMIN) {
            return back()->withErrors(['نقش super_admin قابل حذف نیست.']);
        }

        if ($role->users_count > 0) {
            return back()->withErrors(['این نقش به کاربران اختصاص داده شده و قابل حذف نیست.']);
        }

        $role->delete();

        // Clear all related caches
        Role::clearAllCaches();

        return redirect()->route('roles.index')->with('success', 'نقش با موفقیت حذف شد.');
    }
}
