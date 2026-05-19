<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
             $table->string('full_insurance_amount')->nullable()->after('deposit');

            $table->string('additional_driver_amount')->nullable()->after('full_insurance_amount');

            $table->string('baby_seat_amount')->nullable()->after('additional_driver_amount');

            $table->string('deposit_amount')->nullable()->after('baby_seat_amount');

            $table->string('waiver_amount')->nullable()->after('deposit_amount');
            
            $table->string('different_city_dropoff_fee')->nullable()->after('waiver_amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn([
                'full_insurance_amount',
                'additional_driver_amount',
                'baby_seat_amount',
                'deposit_amount',
                'waiver_amount',
                'different_city_dropoff_fee'
            ]);
        });
    }
};
