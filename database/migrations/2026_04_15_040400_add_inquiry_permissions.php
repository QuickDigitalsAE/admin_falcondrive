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
        foreach (['Inquiry_Menu','Inquiry_ViewAll','Inquiry_ViewMine','Inquiry_View','Inquiry_Add','Inquiry_Edit','Inquiry_Delete','Inquiry_Revoke'] as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert(['name' => $permission, 'guard_name' => 'web', 'table_name' => 'inquiries', 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->whereIn('name', ['Inquiry_Menu','Inquiry_ViewAll','Inquiry_ViewMine','Inquiry_View','Inquiry_Add','Inquiry_Edit','Inquiry_Delete','Inquiry_Revoke'])->where('guard_name', 'web')->delete();
    }
};
