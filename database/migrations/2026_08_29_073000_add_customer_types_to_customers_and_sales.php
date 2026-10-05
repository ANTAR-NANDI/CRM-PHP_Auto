<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->default('retail')->after('name')->index();
            $table->string('email')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('due_balance');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_type')->default('walking')->after('customer_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('sales', fn (Blueprint $table) => $table->dropColumn('customer_type'));
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn(['customer_type', 'email', 'is_active']));
    }
};
