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
        foreach (['Delivery_Return_Location_Menu', 'Delivery_Return_Location_ViewAll', 'Delivery_Return_Location_View', 'Delivery_Return_Location_Add', 'Delivery_Return_Location_Edit', 'Delivery_Return_Location_Delete'] as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'table_name' => 'delivery_return_locations',
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
            ->whereIn('name', ['Delivery_Return_Location_Menu', 'Delivery_Return_Location_ViewAll', 'Delivery_Return_Location_View', 'Delivery_Return_Location_Add', 'Delivery_Return_Location_Edit', 'Delivery_Return_Location_Delete'])
            ->where('guard_name', 'web')
            ->delete();
    }
};
