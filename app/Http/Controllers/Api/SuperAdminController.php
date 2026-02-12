<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    // Get dashboard stats
    public function stats(Request $request)
    {
        $companyId = $request->user()->company_id;
        $stats = [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_menus' => Menu::count(),
            'recent_users' => User::take(10)
                ->select('id', 'name', 'email', 'created_at')
                ->get(),
            'role_distribution' => Role::where('company_id', $companyId)
                ->withCount('users')
                ->get()
                ->map(fn($role) => [
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                ]),
        ];
        return response()->json($stats);
    }
    // Get menu with role restrictions for current user
    public function getMenus(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $userRoleIds = $user->roles()->pluck('roles.id')->toArray();
        $menus = Menu::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with(['children'])
            ->get()
            ->filter(function ($menu) use ($userRoleIds) {
                $menuRoleIds = $menu->roles()->pluck('roles.id')->toArray();
                if (empty($menuRoleIds)) {
                    return true;
                }
                return !empty(array_intersect($userRoleIds, $menuRoleIds));
            })
            ->values();
        return response()->json($menus);
    }

    // Check if user is superadmin
    public function isSuperAdmin(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        return response()->json([
            'is_superadmin' => $isSuperAdmin,
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }
}
