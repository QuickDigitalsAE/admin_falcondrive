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
        foreach (['Booking_Menu', 'Booking_ViewAll', 'Booking_View', 'Booking_Add', 'Booking_Edit', 'Booking_Delete'] as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'table_name' => 'bookings',
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
            ->whereIn('name', ['Booking_Menu', 'Booking_ViewAll', 'Booking_View', 'Booking_Add', 'Booking_Edit', 'Booking_Delete'])
            ->where('guard_name', 'web')
            ->delete();
    }
};

