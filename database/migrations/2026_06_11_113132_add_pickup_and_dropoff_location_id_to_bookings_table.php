<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'self_pickup_location_id')) {
                $table->unsignedBigInteger('self_pickup_location_id')->nullable()->after('self_pickup_location');
            }

            if (!Schema::hasColumn('bookings', 'self_return_location_id')) {
                $table->unsignedBigInteger('self_return_location_id')->nullable()->after('self_return_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'self_pickup_location_id')) {
                $table->dropColumn('self_pickup_location_id');
            }

            if (Schema::hasColumn('bookings', 'self_return_location_id')) {
                $table->dropColumn('self_return_location_id');
            }
        });
    }
};
