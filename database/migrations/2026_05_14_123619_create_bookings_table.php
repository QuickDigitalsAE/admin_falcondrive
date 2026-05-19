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
            $table->decimal('rental_price', 10, 2)->default(0);
            $table->string('rental_duration')->nullable()->comment('Calculated rental duration (e.g., "3 days", "1 week")');
            $table->enum('resident_tourist', ['resident', 'tourist'])->nullable();

            // Insurance & Add-ons
            $table->boolean('full_insurance')->default(false);
            $table->decimal('full_insurance_price', 10, 2)->default(0);
            $table->boolean('additional_driver')->default(false);
            $table->decimal('additional_driver_charges', 10, 2)->default(0);
            $table->boolean('baby_seat')->default(false);
            $table->decimal('baby_seat_price', 10, 2)->default(0);
            $table->enum('deposit_waiver', ['Deposit', 'Waiver'])->nullable();
            $table->decimal('deposit_waiver_price', 10, 2)->default(0);

            // Logistics
            $table->text('delivery_location')->nullable();
            $table->decimal('delivery_location_price', 10, 2)->default(0);
            $table->decimal('different_city_dropoff_fee', 10, 2)->default(0);
            $table->text('self_pickup_location')->nullable();
            $table->string('self_pickup_address')->nullable();
            $table->text('return_location')->nullable();
            $table->decimal('return_location_price', 10, 2)->default(0);
            $table->text('self_return_location')->nullable();
            $table->string('self_return_address')->nullable();

            // Financials & Payment
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_amount', 10, 2)->default(0);
            $table->decimal('pay_now_discount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('payment_flow', ['now', 'later'])->default('later');
            $table->decimal('pay_now_20%_to_Reserve', 10, 2)->default(0);
            $table->decimal('pay_at_pickup_80%', 10, 2)->default(0);
            $table->string('paid_id')->nullable()->comment('Transaction ID from gateway');
            $table->dateTime('paid_date')->nullable();
            $table->string('paid_status')->nullable();
            $table->string('paid_via')->nullable()->comment('EPG, Stripe, PayPal, Cash, etc.');

            // Contact & Terms
            $table->enum('contact_preference', ['whatsapp', 'phone'])->nullable();
            $table->boolean('term_22_years')->default(false);
            $table->boolean('term_6_month_experience')->default(false);

            // Documentation & Logs
            $table->string('send_booking_id')->nullable();
            $table->text('notes')->nullable();
            $table->longText('speed_response')->nullable()->comment('Full API return response from Speed for debugging');
            
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
