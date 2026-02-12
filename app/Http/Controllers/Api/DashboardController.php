<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        // Basic Stats
        $totalUsers = User::where('company_id', $companyId)->count();
        $totalRoles = Role::where('company_id', $companyId)->count();
        $totalMenus = Menu::where('company_id', $companyId)->count();
        // Role distribution
        $roleDistribution = Role::where('company_id', $companyId)
            ->withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'role' => $role->name,
                    'users' => $role->users_count
                ];
            });
        // Latest users
        $recentUsers = User::where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        return response()->json([
            'company' => [
                'id' => $user->company_id,
                'name' => $user->company->name ?? null,
            ],
            'stats' => [
                'total_users' => $totalUsers,
                'total_roles' => $totalRoles,
                'total_menus' => $totalMenus,
            ],
            'role_distribution' => $roleDistribution,
            'recent_users' => $recentUsers,
        ]);
    }
}
