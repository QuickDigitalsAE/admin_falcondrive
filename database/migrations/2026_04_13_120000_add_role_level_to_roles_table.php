<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('role_level', ['superadmin', 'admin', 'manager', 'sales'])
                ->default('admin')
                ->after('guard_name');
        });

        DB::table('roles')
            ->where(function ($query) {
                $query->where('name', 'Super Administrator')
                    ->orWhere('name', 'Super Admin');
            })
            ->update(['role_level' => 'superadmin']);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('role_level');
        });
    }
};
