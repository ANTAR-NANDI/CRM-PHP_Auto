<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('crm_locations', 'location_type')) {
            Schema::table('crm_locations', fn (Blueprint $table) => $table->string('location_type', 20)->default('division')->after('name'));
        }

        if (! Schema::hasColumn('crm_locations', 'parent_id')) {
            Schema::table('crm_locations', fn (Blueprint $table) => $table->foreignId('parent_id')->nullable()->after('location_type')->constrained('crm_locations')->nullOnDelete());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('crm_locations', 'parent_id')) {
            Schema::table('crm_locations', fn (Blueprint $table) => $table->dropConstrainedForeignId('parent_id'));
        }
        if (Schema::hasColumn('crm_locations', 'location_type')) {
            Schema::table('crm_locations', fn (Blueprint $table) => $table->dropColumn('location_type'));
        }
    }
};
