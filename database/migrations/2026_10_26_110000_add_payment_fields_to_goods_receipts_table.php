<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('cash_account_id')->nullable()->after('cash_head')->constrained('chart_of_accounts')->nullOnDelete();
            $table->decimal('paid_amount', 14, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_account_id');
            $table->dropColumn('paid_amount');
        });
    }
};
