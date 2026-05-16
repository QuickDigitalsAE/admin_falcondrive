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
            'Car_Menu',
            'Car_ViewAll',
            'Car_ViewMine',
            'Car_View',
            'Car_Add',
            'Car_Edit',
            'Car_Delete',
            'Car_Revoke',
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
                    'table_name' => 'cars',
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
                'Car_Menu',
                'Car_ViewAll',
                'Car_ViewMine',
                'Car_View',
                'Car_Add',
                'Car_Edit',
                'Car_Delete',
                'Car_Revoke',
            ])
            ->where('guard_name', 'web')
            ->delete();
    }
};
