<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_en');
            $table->string('title_ar');
            $table->longText('blog_description_en')->nullable();
            $table->longText('blog_description_ar')->nullable();
            $table->string('slug')->unique();
            $table->string('seo_title_en')->nullable();
            $table->string('seo_title_ar')->nullable();
            $table->text('seo_brief_en')->nullable();
            $table->text('seo_brief_ar')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('blog_schedule')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('title_en');
            $table->index('title_ar');
            $table->index('blog_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
