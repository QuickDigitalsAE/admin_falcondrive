<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('car_id');
            $table->integer('category_id');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['car_id', 'category_id']);
            $table->index('car_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_category');
    }
};
