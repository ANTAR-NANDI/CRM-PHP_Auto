<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id(); $table->string('requisition_no')->unique(); $table->date('requisition_date'); $table->text('remarks')->nullable(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->timestamps();
        });
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('requisition_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->decimal('quantity', 14, 2); $table->timestamps(); $table->unique(['requisition_id', 'product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('requisition_items'); Schema::dropIfExists('requisitions'); }
};
