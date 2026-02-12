<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // Get all permissions
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }
    // Get single permission
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }
    // Create permission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions',
            'slug' => 'required|string|unique:permissions',
        ]);

        $permission = Permission::create($validated);
        return response()->json(['message' => 'Permission created', 'permission' => $permission], 201);
    }
    // Update permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
            'slug' => 'required|string|unique:permissions,slug,' . $id,
        ]);

        $permission->update($validated);
        return response()->json(['message' => 'Permission updated', 'permission' => $permission]);
    }

    // Delete permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(['message' => 'Permission deleted']);
    }
}
