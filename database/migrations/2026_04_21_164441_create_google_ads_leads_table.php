<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('google_ads_leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead_id')->unique();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('form_id')->nullable();
            $table->string('gcl_id')->nullable();

            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 100)->nullable();

            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->string('country')->nullable();
            $table->string('work_email')->nullable();

            $table->string('lead_stage', 100)->nullable();
            $table->dateTime('lead_submit_time')->nullable();

            $table->boolean('is_test')->default(false);

            $table->longText('raw_payload');
            $table->longText('parsed_fields_json')->nullable();

            $table->string('api_version', 191)->nullable();

            $table->unsignedBigInteger('adgroup_id')->nullable();
            $table->unsignedBigInteger('creative_id')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_ads_leads');
    }
};