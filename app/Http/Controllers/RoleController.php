<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // super_admin is always locked — admin/manager/cashier are editable
    private static array $lockedRoles  = ['super_admin'];
    private static array $systemRoles  = ['super_admin', 'admin', 'manager', 'cashier'];

    private static array $modulePermissions = [
        'purchases'    => ['view suppliers', 'manage suppliers', 'view purchases', 'create purchases', 'manage purchases', 'view grn', 'create grn'],
        'expenses'     => ['view expenses', 'manage expenses'],
        'sale_returns' => ['process sale returns'],
        'barcodes'     => ['print barcodes'],
        'reports'      => ['view reports'],
        'customers'    => ['view customers', 'manage customers'],
    ];

    public function index()
    {
        $systemRoles = Role::withCount('users')
            ->whereIn('name', ['admin', 'manager', 'cashier'])
            ->orderByRaw("FIELD(name,'admin','manager','cashier')")->get();

        $customRoles = Role::withCount('users')
            ->whereNotIn('name', self::$systemRoles)
            ->orderBy('name')->get();

        foreach ($systemRoles->merge($customRoles) as $role) {
            $role->permissionsList = $role->permissions->pluck('name')->toArray();
        }

        return view('roles.index', compact('systemRoles', 'customRoles'));
    }

    public function create()
    {
        $grouped   = $this->groupPermissions($this->activePermissions());
        $rolePerms = [];
        return view('roles.form', compact('grouped', 'rolePerms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name|regex:/^[a-zA-Z0-9 _-]+$/',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->permissions) {
            $role->syncPermissions(Permission::whereIn('id', $request->permissions)->get());
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' create ho gaya!");
    }

    public function edit(Role $role)
    {
        if (in_array($role->name, self::$lockedRoles)) {
            return redirect()->route('roles.index')->with('error', 'Super Admin ko edit nahi kar sakte.');
        }

        $grouped   = $this->groupPermissions($this->activePermissions());
        $rolePerms = $role->permissions->pluck('id')->toArray();

        return view('roles.form', compact('role', 'grouped', 'rolePerms'));
    }

    public function update(Request $request, Role $role)
    {
        if (in_array($role->name, self::$lockedRoles)) {
            return redirect()->route('roles.index')->with('error', 'Super Admin ko edit nahi kar sakte.');
        }

        $isSystem = in_array($role->name, self::$systemRoles);

        $request->validate([
            'name'        => $isSystem ? 'sometimes' : 'required|string|max:50|unique:roles,name,' . $role->id . '|regex:/^[a-zA-Z0-9 _-]+$/',
            'permissions' => 'array',
        ]);

        // System roles (admin/manager/cashier) — only update permissions, not name
        if (!$isSystem) {
            $oldName = $role->name;
            $role->update(['name' => $request->name]);
            if ($oldName !== $request->name) {
                \App\Models\User::where('role', $oldName)->update(['role' => $request->name]);
            }
        }

        $perms = $request->permissions
            ? Permission::whereIn('id', $request->permissions)->get()
            : [];
        $role->syncPermissions($perms);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role update ho gaya!');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, self::$systemRoles)) {
            return redirect()->route('roles.index')->with('error', 'System roles (Admin/Manager/Cashier) ko delete nahi kar sakte.');
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()->route('roles.index')
                ->with('error', "Is role pe {$userCount} user(s) assigned hain. Pehle unka role change karo.");
        }

        $name = $role->name;
        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role '{$name}' delete ho gaya!");
    }

    private function activePermissions()
    {
        $disabled = [];
        foreach (self::$modulePermissions as $key => $perms) {
            if (!Module::isActive($key)) {
                array_push($disabled, ...$perms);
            }
        }
        return Permission::orderBy('name')
            ->when($disabled, fn($q) => $q->whereNotIn('name', $disabled))
            ->get();
    }

    private function groupPermissions($permissions)
    {
        return $permissions->groupBy(function ($p) {
            if (str_starts_with($p->name, 'manage '))  return 'Manage';
            if (str_starts_with($p->name, 'create '))  return 'Create';
            if (str_starts_with($p->name, 'edit '))    return 'Edit';
            if (str_starts_with($p->name, 'delete '))  return 'Delete';
            if (str_starts_with($p->name, 'view '))    return 'View';
            if (str_starts_with($p->name, 'add '))     return 'Stock';
            if (str_starts_with($p->name, 'adjust '))  return 'Stock';
            if (str_starts_with($p->name, 'print '))   return 'Barcode';
            if (str_starts_with($p->name, 'process ')) return 'Sales';
            if ($p->name === 'pos sales')               return 'Sales';
            return 'Other';
        });
    }
}