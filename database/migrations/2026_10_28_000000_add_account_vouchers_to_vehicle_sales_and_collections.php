<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->foreignId('account_voucher_id')->nullable()->after('payment_mode')->constrained('account_vouchers')->nullOnDelete();
        });

        Schema::table('sales_collections', function (Blueprint $table) {
            $table->foreignId('account_voucher_id')->nullable()->after('bank_head')->constrained('account_vouchers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_voucher_id');
        });

        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_voucher_id');
        });
    }
};
