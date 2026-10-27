<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('customer_vehicle_sale_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('cleared_cash_account_id')->nullable()->after('sales_collection_id')->constrained('chart_of_accounts')->nullOnDelete();
            $table->date('cleared_at')->nullable()->after('cheque_date');
        });
    }

    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('customer_vehicle_sale_id');
            $table->dropConstrainedForeignId('cleared_cash_account_id');
            $table->dropColumn('cleared_at');
        });
    }
};
