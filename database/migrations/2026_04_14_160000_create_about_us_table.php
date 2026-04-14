<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->longText('first_section_en');
            $table->longText('first_section_ar');
            $table->longText('mission_en');
            $table->longText('mission_ar');
            $table->longText('vision_en');
            $table->longText('vision_ar');
            $table->string('seo_title_en');
            $table->string('seo_title_ar');
            $table->text('seo_brief_en');
            $table->text('seo_brief_ar');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
