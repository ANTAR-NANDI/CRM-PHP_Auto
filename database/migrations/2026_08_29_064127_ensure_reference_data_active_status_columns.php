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
        foreach (['suppliers', 'brands', 'generic_names'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->boolean('is_active')->default(true));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['suppliers', 'brands', 'generic_names'] as $tableName) {
            if (Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('is_active'));
            }
        }
    }
};
