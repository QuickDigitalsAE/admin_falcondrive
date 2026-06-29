<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'pickup_location_id')) {
                $table->unsignedBigInteger('pickup_location_id')->nullable()->after('deposit_waiver_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'pickup_location_id')) {
                $table->dropColumn('pickup_location_id');
            }
        });
    }
};
