<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Get all users
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $query = User::all();
        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        }
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        return response()->json($query);
    }

    // Get single user
    public function show(Request $request, $id)
    {
        $user = User::with('roles')
            ->findOrFail($id);
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    // Create user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'roles' => 'array',
        ]);

        $companyId = $request->user()->company_id;
        $validated['company_id'] = $companyId;
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }
        $user->load('roles');
        return response()->json(['message' => 'User created', 'user' => $user], 201);
    }

    // Update user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Check authorization
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'roles' => 'array',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        $user->load('roles');
        return response()->json(['message' => 'User updated', 'user' => $user]);
    }

    // Delete user
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // Check authorization
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Prevent deleting yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    // Assign roles to user
    public function assignRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($validated['roles']);
        $user->load('roles');
        return response()->json(['message' => 'Roles assigned', 'user' => $user]);
    }

    // Get user's permissions
    public function getPermissions($id)
    {
        $user = User::with('roles.permissions')->findOrFail($id);
        $permissions = $user->roles
            ->flatMap(fn($role) => $role->permissions)
            ->unique('id')
            ->values();
        return response()->json([
            'user_id' => $user->id,
            'permissions' => $permissions,
        ]);
    }
}
