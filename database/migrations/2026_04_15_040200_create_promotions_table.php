<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_en');
            $table->string('name_ar');
            $table->longText('description_en')->nullable();
            $table->longText('description_ar')->nullable();
            $table->string('seo_title_en');
            $table->string('seo_title_ar');
            $table->text('seo_brief_en');
            $table->text('seo_brief_ar');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->mediumInteger('top_offer')->nullable()->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
