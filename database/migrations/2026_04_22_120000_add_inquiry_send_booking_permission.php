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

        if (DB::table('permissions')->where('name', 'Inquiry_SendBooking')->where('guard_name', 'web')->exists()) {
            return;
        }

        $now = Carbon::now();

        DB::table('permissions')->insert([
            'name' => 'Inquiry_SendBooking',
            'guard_name' => 'web',
            'table_name' => 'inquiries',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        DB::table('permissions')
            ->where('name', 'Inquiry_SendBooking')
            ->where('guard_name', 'web')
            ->delete();
    }
};
