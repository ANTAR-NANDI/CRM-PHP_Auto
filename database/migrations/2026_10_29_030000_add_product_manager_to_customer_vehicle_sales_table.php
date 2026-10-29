<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_vehicle_sales', function (Blueprint $table) {
            $table->foreignId('product_manager_id')->nullable()->after('sales_person_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_vehicle_sales', fn (Blueprint $table) => $table->dropConstrainedForeignId('product_manager_id'));
    }
};
