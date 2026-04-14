<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'Dashboard_View',
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
            'Role_Revoke',
            'Permissions_Menu',
            'Permissions_ViewAll',
            'Permissions_View',
            'Permissions_Add',
            'Permissions_Edit',
            'Permissions_Delete',
            'Permissions_Revoke',
            'ActivityLogs_Menu',
            'ActivityLogs_ViewAll',
            'ActivityLogs_View',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'table_name' => str($permission)->before('_')->snake()->plural()->toString(),
            ]);
        }
    }
}
