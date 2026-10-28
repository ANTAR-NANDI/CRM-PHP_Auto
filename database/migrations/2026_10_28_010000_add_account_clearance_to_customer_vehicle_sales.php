<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->string('account_clearance_status')->default('pending')->after('account_voucher_id');
            $table->foreignId('account_cleared_by')->nullable()->after('account_clearance_status')->constrained('users')->nullOnDelete();
            $table->timestamp('account_cleared_at')->nullable()->after('account_cleared_by');
            $table->text('account_clearance_note')->nullable()->after('account_cleared_at');
            $table->index('account_clearance_status');
        });
    }

    public function down(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->dropForeign(['account_cleared_by']);
            $table->dropIndex(['account_clearance_status']);
            $table->dropColumn(['account_clearance_status', 'account_cleared_by', 'account_cleared_at', 'account_clearance_note']);
        });
    }
};
