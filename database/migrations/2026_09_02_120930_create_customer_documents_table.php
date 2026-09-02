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
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('customer_id');

            $table->text('customer_details')->nullable();

            $table->string('document_no')->nullable();

            $table->string('issue_date')->nullable();

            $table->string('expiry_date')->nullable();

            $table->string('issued_by')->nullable();

            $table->string('identity_name')->nullable();

            $table->integer('identity_document_id')->nullable();

            $table->text('description')->nullable();

            $table->string('data')->nullable();


            // Store uploaded file path
            $table->longText('document')->nullable();


            $table->string('file_name')->nullable();

            $table->string('file_name_without_extension')->nullable();


            $table->enum('status',[
                'pending',
                'approved'
            ])->default('pending');


            $table->integer('created_by')->nullable();

            $table->integer('updated_by')->nullable();

            $table->integer('deleted_by')->nullable();


            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
    }
};
