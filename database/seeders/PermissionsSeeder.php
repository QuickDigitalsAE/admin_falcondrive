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
            'AboutUs_Menu',
            'AboutUs_ViewAll',
            'AboutUs_ViewMine',
            'AboutUs_View',
            'AboutUs_Add',
            'AboutUs_Edit',
            'AboutUs_Delete',
            'AboutUs_Revoke',
            'Brand_Menu',
            'Brand_ViewAll',
            'Brand_ViewMine',
            'Brand_View',
            'Brand_Add',
            'Brand_Edit',
            'Brand_Delete',
            'Brand_Revoke',
            'Category_Menu',
            'Category_ViewAll',
            'Category_ViewMine',
            'Category_View',
            'Category_Add',
            'Category_Edit',
            'Category_Delete',
            'Category_Revoke',
            'Faq_Menu',
            'Faq_ViewAll',
            'Faq_ViewMine',
            'Faq_View',
            'Faq_Add',
            'Faq_Edit',
            'Faq_Delete',
            'Faq_Revoke',
            'Lease_Menu',
            'Lease_ViewAll',
            'Lease_ViewMine',
            'Lease_View',
            'Lease_Add',
            'Lease_Edit',
            'Lease_Delete',
            'Lease_Revoke',
            'Location_Menu',
            'Location_ViewAll',
            'Location_ViewMine',
            'Location_View',
            'Location_Add',
            'Location_Edit',
            'Location_Delete',
            'Location_Revoke',
            'Testimonial_Menu',
            'Testimonial_ViewAll',
            'Testimonial_ViewMine',
            'Testimonial_View',
            'Testimonial_Add',
            'Testimonial_Edit',
            'Testimonial_Delete',
            'Testimonial_Revoke',
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
