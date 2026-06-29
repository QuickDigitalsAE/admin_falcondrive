<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('delivery_return_locations', 'city') && !Schema::hasColumn('delivery_return_locations', 'title')) {
            Schema::table('delivery_return_locations', function (Blueprint $table) {
                $table->renameColumn('city', 'title');
            });
        }

        Schema::table('delivery_return_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_return_locations', 'detail')) {
                $table->text('detail')->nullable()->after('title');
            }

            if (!Schema::hasColumn('delivery_return_locations', 'web_id')) {
                $table->string('web_id')->nullable()->after('detail');
            }

            if (!Schema::hasColumn('delivery_return_locations', 'pickup_location_id')) {
                $table->unsignedBigInteger('pickup_location_id')->nullable()->after('web_id');
            }

            if (!Schema::hasColumn('delivery_return_locations', 'longitude')) {
                $table->decimal('longitude', 10, 8)->nullable()->after('pickup_location_id');
            }

            if (!Schema::hasColumn('delivery_return_locations', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_return_locations', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_return_locations', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('delivery_return_locations', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('delivery_return_locations', 'web_id')) {
                $table->dropColumn('web_id');
            }

            if (Schema::hasColumn('delivery_return_locations', 'pickup_location_id')) {
                $table->dropColumn('pickup_location_id');
            }

            if (Schema::hasColumn('delivery_return_locations', 'detail')) {
                $table->dropColumn('detail');
            }
        });

        if (Schema::hasColumn('delivery_return_locations', 'title') && !Schema::hasColumn('delivery_return_locations', 'city')) {
            Schema::table('delivery_return_locations', function (Blueprint $table) {
                $table->renameColumn('title', 'city');
            });
        }
    }
};
