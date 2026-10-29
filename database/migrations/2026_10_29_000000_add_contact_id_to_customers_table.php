<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach (['Lead', 'Customer'] as $name) {
            DB::table('crm_contact_types')->updateOrInsert(['name' => $name], ['is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('id')->constrained()->nullOnDelete()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropUnique(['contact_id']);
            $table->dropColumn('contact_id');
        });
    }
};
