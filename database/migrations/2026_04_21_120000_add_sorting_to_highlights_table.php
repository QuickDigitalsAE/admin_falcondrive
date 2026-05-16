<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('highlights', function (Blueprint $table) {
            $table->integer('sorting')->nullable()->after('image');
            $table->index('sorting');
        });
    }

    public function down(): void
    {
        Schema::table('highlights', function (Blueprint $table) {
            $table->dropIndex(['sorting']);
            $table->dropColumn('sorting');
        });
    }
};
