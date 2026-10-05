<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->foreignId('chart_of_account_id')->nullable()->after('due_balance')->constrained('chart_of_accounts')->nullOnDelete());
        Schema::table('suppliers', fn (Blueprint $table) => $table->foreignId('chart_of_account_id')->nullable()->after('is_active')->constrained('chart_of_accounts')->nullOnDelete());
        Schema::table('users', fn (Blueprint $table) => $table->foreignId('chart_of_account_id')->nullable()->after('id')->constrained('chart_of_accounts')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('chart_of_account_id'));
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropConstrainedForeignId('chart_of_account_id'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropConstrainedForeignId('chart_of_account_id'));
    }
};
