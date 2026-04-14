<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the admin roles using firstOrCreate
        $role = Role::firstOrCreate(
            ['name' => 'Super Administrator', 'guard_name' => 'web'],
            ['role_level' => 'superadmin']
        );

        if ($role->role_level !== 'superadmin') {
            $role->role_level = 'superadmin';
            $role->save();
        }

        // Retrieve all permissions
        $permissions = Permission::all();

        // // Sync all permissions to the admin role
        $role->syncPermissions($permissions);

        // Retrieve the admin role
        $adminRole = Role::where('name', 'Super Administrator')->first();

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'status' => 1,
                'password' => Hash::make('admin123@'),
            ]
        );

        $admin->assignRole($adminRole);
    }
}
