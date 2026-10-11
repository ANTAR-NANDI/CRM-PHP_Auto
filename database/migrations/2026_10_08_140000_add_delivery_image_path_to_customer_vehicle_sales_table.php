<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->string('delivery_image_path')->nullable()->after('payment_mode');
        });
    }

    public function down(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->dropColumn('delivery_image_path');
        });
    }
};
