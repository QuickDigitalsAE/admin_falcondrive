<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('delivery_custom_address')->nullable()->after('delivery_location');
            $table->text('return_custom_address')->nullable()->after('return_location');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_custom_address',
                'return_custom_address',
            ]);
        });
    }
};
