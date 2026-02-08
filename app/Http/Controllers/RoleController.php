<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    private function getGroupedPermissions($permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'general';

            if (! isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }

    public function index(Request $request): View
    {
        $roles = Role::withCount(['permissions', 'users'])->orderByDesc('id')->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->getGroupedPermissions($permissions);

        return view('roles.create', compact('permissions', 'groupedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permission' => 'required|array|min:1',
            'permission.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $permissions = Permission::whereIn('id', $validated['permission'])->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function show($id): View
    {
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions()->orderBy('name')->get();

        return view('roles.show', compact('role', 'rolePermissions'));
    }

    public function edit($id): View
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->getGroupedPermissions($permissions);
        $rolePermissions = $role->permissions()->pluck('permissions.id')->map(fn ($id) => (int) $id)->all();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'groupedPermissions'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permission' => 'required|array|min:1',
            'permission.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $validated['name'];
        $role->save();
        $permissions = Permission::whereIn('id', $validated['permission'])->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}
