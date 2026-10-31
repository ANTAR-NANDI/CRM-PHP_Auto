<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('delivery_orders', function (Blueprint $table) { $table->id(); $table->foreignId('customer_vehicle_sale_id')->unique()->constrained()->cascadeOnDelete(); $table->string('do_no')->unique(); $table->date('do_date'); $table->foreignId('attention_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('delivery_place')->nullable(); $table->text('remarks')->nullable(); $table->string('engine_no')->nullable(); $table->string('chassis_no')->nullable(); $table->text('note')->nullable(); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('delivery_orders'); }
};
