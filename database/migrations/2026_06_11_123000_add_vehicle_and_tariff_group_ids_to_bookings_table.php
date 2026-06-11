<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('vehicle_group_id')->nullable()->after('term_6_month_experience');
            $table->unsignedInteger('tariff_group_id')->nullable()->after('vehicle_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_group_id',
                'tariff_group_id',
            ]);
        });
    }
};
