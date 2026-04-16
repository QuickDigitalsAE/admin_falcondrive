<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        $now = Carbon::now();
        $permissions = [
            'CarWithDriver_Menu',
            'CarWithDriver_ViewAll',
            'CarWithDriver_ViewMine',
            'CarWithDriver_View',
            'CarWithDriver_Add',
            'CarWithDriver_Edit',
            'CarWithDriver_Delete',
            'CarWithDriver_Revoke',
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')
                ->where('name', $permission)
                ->where('guard_name', 'web')
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'table_name' => 'car_with_drivers',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        DB::table('permissions')
            ->whereIn('name', [
                'CarWithDriver_Menu',
                'CarWithDriver_ViewAll',
                'CarWithDriver_ViewMine',
                'CarWithDriver_View',
                'CarWithDriver_Add',
                'CarWithDriver_Edit',
                'CarWithDriver_Delete',
                'CarWithDriver_Revoke',
            ])
            ->where('guard_name', 'web')
            ->delete();
    }
};
