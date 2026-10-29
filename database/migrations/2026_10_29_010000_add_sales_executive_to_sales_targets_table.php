<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->foreignId('sales_executive_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->index(['sales_executive_id', 'target_year', 'target_month']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropIndex(['sales_executive_id', 'target_year', 'target_month']);
            $table->dropConstrainedForeignId('sales_executive_id');
        });
    }
};
