<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            // Keep this column flexible across environments where users.id may differ.
            // Notifications should also survive user soft-deletes, so a hard FK is unnecessary here.
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('fa-bell');
            $table->string('color', 30)->default('amber');
            $table->string('url')->nullable();
            $table->string('category', 50)->default('activity');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
