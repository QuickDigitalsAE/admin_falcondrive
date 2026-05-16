<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_with_drivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('slug');
            $table->text('display_en');
            $table->text('display_ar');
            $table->text('meta_title_en');
            $table->text('meta_description_en');
            $table->text('meta_title_ar');
            $table->text('meta_description_ar');
            $table->text('card_image')->nullable();
            $table->text('card_header_en')->nullable();
            $table->text('card_text_en')->nullable();
            $table->text('card_header_ar')->nullable();
            $table->text('card_text_ar')->nullable();
            $table->text('header_en');
            $table->text('header_ar');
            $table->text('cars')->nullable();
            $table->longText('content_en');
            $table->longText('content_ar');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_with_drivers');
    }
};
