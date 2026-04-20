<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->integer('sorting')->nullable()->after('logo');
        });

        $brands = DB::table('brands')
            ->orderBy('id')
            ->pluck('id');

        foreach ($brands as $index => $brandId) {
            DB::table('brands')
                ->where('id', $brandId)
                ->update(['sorting' => $index]);
        }
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('sorting');
        });
    }
};
