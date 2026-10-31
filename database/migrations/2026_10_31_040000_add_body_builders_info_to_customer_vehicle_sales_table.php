<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('customer_vehicle_sales', fn (Blueprint $table) => $table->string('body_builders_info')->nullable()->after('body_type')); }
    public function down(): void { Schema::table('customer_vehicle_sales', fn (Blueprint $table) => $table->dropColumn('body_builders_info')); }
};
