<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('minimum_amount', 10, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
