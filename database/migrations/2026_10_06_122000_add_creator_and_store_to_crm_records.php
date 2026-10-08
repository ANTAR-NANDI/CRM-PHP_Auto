<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['crm_organizations', 'contacts', 'leads'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                });
            }

            if (! Schema::hasColumn($tableName, 'store_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['crm_organizations', 'contacts', 'leads'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
                $table->dropConstrainedForeignId('store_id');
            });
        }
    }
};
