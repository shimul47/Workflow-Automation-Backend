<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Get all menus
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $menus = Menu::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('children', 'roles')
            ->orderBy('order')
            ->get();
        return response()->json($menus);
    }
    public function all(Request $request)
    {
        $companyId = $request->user()->company_id;
        $menus = Menu::where('company_id', $companyId)
            ->with('roles')
            ->orderBy('order')
            ->get();
        return response()->json($menus);
    }
    // Get single menu
    public function show($id)
    {
        $menu = Menu::with('children', 'roles', 'parent')->findOrFail($id);
        return response()->json($menu);
    }
    // Create menu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'slug' => 'required|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'roles' => 'array',
        ]);
        $companyId = $request->user()->company_id;
        $validated['company_id'] = $companyId;
        if (!isset($validated['order'])) {
            $validated['order'] = Menu::max('order') + 1;
        }
        $menu = Menu::create($validated);
        if (isset($validated['roles'])) {
            $menu->roles()->sync($validated['roles']);
        }
        $menu->load('roles');
        return response()->json(['message' => 'Menu created', 'menu' => $menu], 201);
    }
    // Update
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string',
            'slug' => 'required|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'roles' => 'array',
        ]);
        $menu->update($validated);
        if (isset($validated['roles'])) {
            $menu->roles()->sync($validated['roles']);
        }
        $menu->load('roles');
        return response()->json(['message' => 'Menu updated', 'menu' => $menu]);
    }

    // Delete
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        // Delete child menus
        Menu::where('parent_id', $id)->delete();
        $menu->delete();
        return response()->json(['message' => 'Menu deleted']);
    }
    // Assign roles
    public function assignRoles(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);
        $menu->roles()->sync($validated['roles']);
        $menu->load('roles');
        return response()->json(['message' => 'Roles assigned', 'menu' => $menu]);
    }

    // Get menus
    public function myMenus(Request $request)
    {
        $user = $request->user();
        $menus = Menu::where('company_id', $user->company_id)
            ->whereHas('roles', function ($q) use ($user) {
                $q->whereIn('roles.id', $user->roles->pluck('id'));
            })
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();
        return response()->json($menus);
    }

    // Assign menu
    public function assignToRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menu_ids' => 'required|array'
        ]);
        $role = Role::findOrFail($request->role_id);
        $role->menus()->sync($request->menu_ids);
        return response()->json([
            'message' => 'Menus assigned successfully'
        ]);
    }
}
