<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    // Permissions that belong to each module — hidden when module is disabled
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
        $roles       = Role::with('permissions')->orderByRaw("FIELD(name,'super_admin','admin','manager','cashier')")->get();
        $permissions = Permission::orderBy('name')->get();

        // Filter out permissions belonging to disabled modules
        $disabledPerms = [];
        foreach (self::$modulePermissions as $moduleKey => $perms) {
            if (!\App\Helpers\Module::isActive($moduleKey)) {
                array_push($disabledPerms, ...$perms);
            }
        }
        if ($disabledPerms) {
            $permissions = $permissions->filter(fn($p) => !in_array($p->name, $disabledPerms));
        }

        // Group permissions by module prefix
        $grouped = $permissions->groupBy(function ($p) {
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

        return view('permissions.index', compact('roles', 'permissions', 'grouped'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role       = Role::findById($request->role_id);
        $permission = Permission::findById($request->permission_id);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $has = false;
        } else {
            $role->givePermissionTo($permission);
            $has = true;
        }

        // Re-sync users with this role so their permission cache updates
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['has' => $has]);
    }
}