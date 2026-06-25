<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cars', 'full_insurance_amount') && !Schema::hasColumn('cars', 'full_insurance_daily')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->renameColumn('full_insurance_amount', 'full_insurance_daily');
            });
        }

        if (Schema::hasColumn('cars', 'baby_seat_amount') && !Schema::hasColumn('cars', 'baby_seat_daily')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->renameColumn('baby_seat_amount', 'baby_seat_daily');
            });
        }

        if (Schema::hasColumn('cars', 'waiver_amount') && !Schema::hasColumn('cars', 'waiver_daily')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->renameColumn('waiver_amount', 'waiver_daily');
            });
        }

        Schema::table('cars', function (Blueprint $table) {
            if (!Schema::hasColumn('cars', 'full_insurance_weekly')) {
                $table->string('full_insurance_weekly')->nullable()->after('full_insurance_daily');
            }

            if (!Schema::hasColumn('cars', 'full_insurance_monthly')) {
                $table->string('full_insurance_monthly')->nullable()->after('full_insurance_weekly');
            }

            if (!Schema::hasColumn('cars', 'baby_seat_weekly')) {
                $table->string('baby_seat_weekly')->nullable()->after('baby_seat_daily');
            }

            if (!Schema::hasColumn('cars', 'baby_seat_monthly')) {
                $table->string('baby_seat_monthly')->nullable()->after('baby_seat_weekly');
            }

            if (!Schema::hasColumn('cars', 'waiver_weekly')) {
                $table->string('waiver_weekly')->nullable()->after('waiver_daily');
            }

            if (!Schema::hasColumn('cars', 'waiver_monthly')) {
                $table->string('waiver_monthly')->nullable()->after('waiver_weekly');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'full_insurance_daily')) {
                $table->renameColumn('full_insurance_daily', 'full_insurance_amount');
            }

            if (Schema::hasColumn('cars', 'baby_seat_daily')) {
                $table->renameColumn('baby_seat_daily', 'baby_seat_amount');
            }

            if (Schema::hasColumn('cars', 'waiver_daily')) {
                $table->renameColumn('waiver_daily', 'waiver_amount');
            }

            $table->dropColumn([
                'full_insurance_weekly',
                'full_insurance_monthly',
                'baby_seat_weekly',
                'baby_seat_monthly',
                'waiver_weekly',
                'waiver_monthly',
            ]);
        });
    }
};
