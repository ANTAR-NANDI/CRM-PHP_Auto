<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
