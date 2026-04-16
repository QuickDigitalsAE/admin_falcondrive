<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_car_with_driver', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('car_id');
            $table->unsignedBigInteger('car_with_driver_id');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['car_id', 'car_with_driver_id']);
            $table->index('car_id');
            $table->index('car_with_driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_car_with_driver');
    }
};
