<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_return_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('city');
            $table->string('price');
            $table->string('type'); // Delivery location / Return location
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_return_locations');
    }
};
