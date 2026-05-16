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
        foreach (['Setting_Menu', 'Setting_ViewAll', 'Setting_ViewMine', 'Setting_View', 'Setting_Add', 'Setting_Edit', 'Setting_Delete', 'Setting_Revoke'] as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'table_name' => 'settings',
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
            ->whereIn('name', ['Setting_Menu', 'Setting_ViewAll', 'Setting_ViewMine', 'Setting_View', 'Setting_Add', 'Setting_Edit', 'Setting_Delete', 'Setting_Revoke'])
            ->where('guard_name', 'web')
            ->delete();
    }
};
