<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('crm_organizations', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');
            $table->foreignId('location_id')->nullable()->after('email')->constrained('crm_locations')->nullOnDelete();
            $table->string('contact_person')->nullable()->after('location_id');
        });
    }
    public function down(): void { Schema::table('crm_organizations', fn (Blueprint $table) => $table->dropConstrainedForeignId('location_id')->dropColumn(['phone', 'email', 'contact_person'])); }
};
