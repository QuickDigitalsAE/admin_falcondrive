<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_en');
            $table->string('name_ar');
            $table->longText('description_en')->nullable();
            $table->longText('description_ar')->nullable();
            $table->string('price_daily');
            $table->string('price_weekly');
            $table->string('price_monthly');
            $table->string('main_image')->nullable();
            $table->mediumText('images')->nullable();
            $table->string('model');
            $table->integer('featured')->nullable()->default(0);
            $table->string('engine')->nullable();
            $table->string('seats')->nullable();
            $table->string('doors')->nullable();
            $table->string('deposit')->nullable();
            $table->string('luggage')->nullable();
            $table->integer('cruise_control')->nullable()->default(0);
            $table->integer('bluetooth')->nullable()->default(0);
            $table->integer('automatic')->nullable()->default(0);
            $table->integer('parking_sensor')->nullable()->default(0);
            $table->integer('navigation')->nullable()->default(0);
            $table->integer('carplay')->nullable()->default(0);
            $table->integer('camera')->nullable()->default(0);
            $table->string('slug')->unique();
            $table->string('seo_title_en')->nullable();
            $table->string('seo_title_ar')->nullable();
            $table->text('seo_brief_en')->nullable();
            $table->text('seo_brief_ar')->nullable();
            $table->integer('brand_id');
            $table->integer('stock')->default(0);
            $table->string('cdw_daily')->nullable();
            $table->string('cdw_weekly')->nullable();
            $table->string('cdw_monthly')->nullable();
            $table->unsignedInteger('vehicle_group_id')->nullable();
            $table->unsignedInteger('tariff_group_id')->nullable();
            $table->string('sorting')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('name_en');
            $table->index('name_ar');
            $table->index('model');
            $table->index('brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
