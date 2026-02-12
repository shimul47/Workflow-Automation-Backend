<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create superadmin permissions
        $permissions = [
            ['name' => 'Create Users', 'slug' => 'create-users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users'],
            ['name' => 'Create Roles', 'slug' => 'create-roles'],
            ['name' => 'Edit Roles', 'slug' => 'edit-roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles'],
            ['name' => 'Create Permissions', 'slug' => 'create-permissions'],
            ['name' => 'Edit Permissions', 'slug' => 'edit-permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'delete-permissions'],
            ['name' => 'Create Menus', 'slug' => 'create-menus'],
            ['name' => 'Edit Menus', 'slug' => 'edit-menus'],
            ['name' => 'Delete Menus', 'slug' => 'delete-menus'],
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard'],
            ['name' => 'Access Admin Console', 'slug' => 'access-admin-console'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Create default company first
        $company = Company::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Default Company',
                'slug' => 'default-company',
                'is_active' => true
            ]
        );

        // Create superadmin role
        $allPermissions = Permission::all();
        $superadminRole = Role::firstOrCreate(
            ['slug' => 'superadmin'],
            [
                'name' => 'SuperAdmin',
                'company_id' => $company->id,
            ]
        );

        $superadminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create superadmin user
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ]
        );

        // Attach superadmin role
        $user->roles()->sync([$superadminRole->id]);
    }
}
