<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_cars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('locations_id')->nullable();
            $table->integer('car_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['locations_id', 'car_id']);
            $table->index('locations_id');
            $table->index('car_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_cars');
    }
};
