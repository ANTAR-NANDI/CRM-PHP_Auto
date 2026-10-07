<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_issues', function (Blueprint $table) {
            $table->id(); $table->string('issue_no')->unique(); $table->date('issue_date'); $table->foreignId('issue_to_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('store_id')->constrained()->restrictOnDelete(); $table->string('purpose'); $table->text('remarks')->nullable(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->timestamps();
        });
        Schema::create('inventory_issue_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('inventory_issue_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->foreignId('store_position_id')->nullable()->constrained()->nullOnDelete(); $table->decimal('balance_quantity', 14, 2)->default(0); $table->decimal('quantity', 14, 2); $table->string('chassis_no')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_issue_items'); Schema::dropIfExists('inventory_issues'); }
};
