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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('number');
            $table->string('email')->nullable();

            // Rental Period & Type
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('rental_type', ['daily', 'weekly', 'monthly'])->nullable();
            $table->enum('resident_tourist', ['resident', 'tourist'])->nullable();

            // Insurance & Add-ons
            $table->boolean('full_insurance')->default(false);
            $table->boolean('additional_driver')->default(false);
            $table->boolean('baby_seat')->default(false);
            $table->enum('deposit_waiver', ['Deposit', 'Waiver'])->nullable();

            // Logistics
            $table->text('delivery_address')->nullable();
            $table->string('delivery_area')->nullable();
            $table->text('pickup_address')->nullable();
            $table->string('pickup_area')->nullable();
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->decimal('pickup_price', 10, 2)->default(0);

            // Financials & Payment
            $table->string('coupon_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->enum('payment_flow', ['now', 'later'])->default('later');
            $table->string('paid_id')->nullable()->comment('Transaction ID from gateway');
            $table->dateTime('paid_date')->nullable();
            $table->string('paid_status')->nullable();
            $table->string('paid_via')->nullable()->comment('Stripe, PayPal, Cash, etc.');

            // Contact & Terms
            $table->enum('contact_preference', ['whatsapp', 'phone'])->nullable();
            $table->boolean('term_22_years')->default(false);
            $table->boolean('term_6_month_experience')->default(false);

            // Documentation & Logs
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->longText('request_body')->nullable()->comment('Full API request log');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
