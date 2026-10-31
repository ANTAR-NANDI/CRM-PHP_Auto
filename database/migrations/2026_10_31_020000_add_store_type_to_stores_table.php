<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('stores', 'store_type')) {
            Schema::table('stores', fn (Blueprint $table) => $table->string('store_type', 20)->default('warehouse')->after('name'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stores', 'store_type')) {
            Schema::table('stores', fn (Blueprint $table) => $table->dropColumn('store_type'));
        }
    }
};
