<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Get all roles
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $roles = Role::where('company_id', $companyId)
            ->with('permissions', 'users')
            ->get();
        return response()->json($roles);
    }
    // Get single role
    public function show($id)
    {
        $role = Role::with('permissions', 'users', 'menus')->findOrFail($id);
        return response()->json($role);
    }
    // Create role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:roles',
            'permissions' => 'array',
        ]);

        $companyId = $request->user()->company_id;
        $validated['company_id'] = $companyId;

        $role = Role::create($validated);
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }
        $role->load('permissions');
        return response()->json(['message' => 'Role created', 'role' => $role], 201);
    }
    // Update role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:roles,slug,' . $id,
            'permissions' => 'array',
        ]);
        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);
        // Update permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }
        $role->load('permissions');
        return response()->json(['message' => 'Role updated', 'role' => $role]);
    }

    // Delete role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 400);
        }
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }
    // Assign permissions to role
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role->permissions()->sync($validated['permissions']);
        $role->load('permissions');
        return response()->json(['message' => 'Permissions assigned', 'role' => $role]);
    }
}
