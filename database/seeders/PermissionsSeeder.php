<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'User_Menu',
            'User_ViewAll',
            'User_ViewMine',
            'User_View',
            'User_Add',
            'User_Edit',
            'User_Delete',
            'User_Revoke',
            'Role_Menu',
            'Role_ViewAll',
            'Role_View',
            'Role_Add',
            'Role_Edit',
            'Role_Delete',
            'Role_Revoke'
        ];

        // Create the permissions using firstOrCreate
        foreach ($permissions as $permission) {
            Permission::Create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
