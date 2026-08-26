<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->integer('fleet_sorting')->nullable()->after('featured_sorting');
            $table->index('fleet_sorting');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropIndex(['fleet_sorting']);
            $table->dropColumn('fleet_sorting');
        });
    }
};
