<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach (['CustomerDocument_Menu', 'CustomerDocument_ViewAll', 'CustomerDocument_View', 'CustomerDocument_Add', 'CustomerDocument_Edit', 'CustomerDocument_Delete', 'CustomerDocument_Revoke'] as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'table_name' => 'customer_documents',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', ['CustomerDocument_Menu', 'CustomerDocument_ViewAll', 'CustomerDocument_View', 'CustomerDocument_Add', 'CustomerDocument_Edit', 'CustomerDocument_Delete', 'CustomerDocument_Revoke'])->delete();
    }
};
