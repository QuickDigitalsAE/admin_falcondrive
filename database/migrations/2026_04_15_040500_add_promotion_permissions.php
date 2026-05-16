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
        foreach (['Promotion_Menu','Promotion_ViewAll','Promotion_ViewMine','Promotion_View','Promotion_Add','Promotion_Edit','Promotion_Delete','Promotion_Revoke'] as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert(['name' => $permission, 'guard_name' => 'web', 'table_name' => 'promotions', 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->whereIn('name', ['Promotion_Menu','Promotion_ViewAll','Promotion_ViewMine','Promotion_View','Promotion_Add','Promotion_Edit','Promotion_Delete','Promotion_Revoke'])->where('guard_name', 'web')->delete();
    }
};
