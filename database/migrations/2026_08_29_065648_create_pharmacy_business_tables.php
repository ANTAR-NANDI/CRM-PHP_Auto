<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('phone')->nullable()->unique(); $table->text('address')->nullable(); $table->decimal('due_balance', 12, 2)->default(0); $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('barcode')->nullable()->unique(); $table->foreignId('generic_name_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete(); $table->string('unit')->default('piece'); $table->unsignedInteger('reorder_level')->default(0); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['name', 'is_active']);
        });
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete(); $table->string('batch_number')->nullable(); $table->date('expires_on')->nullable(); $table->unsignedInteger('quantity_received'); $table->unsignedInteger('quantity_available'); $table->decimal('purchase_price', 12, 2); $table->decimal('sale_price', 12, 2); $table->timestamps(); $table->index(['product_id', 'expires_on', 'quantity_available']);
        });
        Schema::create('purchases', function (Blueprint $table) {
            $table->id(); $table->string('invoice_number')->unique(); $table->foreignId('supplier_id')->constrained()->restrictOnDelete(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->decimal('subtotal', 12, 2); $table->decimal('discount', 12, 2)->default(0); $table->decimal('total', 12, 2); $table->decimal('paid', 12, 2)->default(0); $table->date('purchased_at')->index(); $table->timestamps();
        });
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('purchase_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->foreignId('medicine_batch_id')->nullable()->constrained()->nullOnDelete(); $table->unsignedInteger('quantity'); $table->decimal('purchase_price', 12, 2); $table->decimal('sale_price', 12, 2); $table->decimal('line_total', 12, 2); $table->timestamps();
        });
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->decimal('opening_cash', 12, 2); $table->decimal('closing_cash', 12, 2)->nullable(); $table->timestamp('opened_at')->useCurrent(); $table->timestamp('closed_at')->nullable(); $table->timestamps();
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); $table->string('invoice_number')->unique(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete(); $table->decimal('subtotal', 12, 2); $table->decimal('discount', 12, 2)->default(0); $table->decimal('total', 12, 2); $table->decimal('paid', 12, 2); $table->decimal('due', 12, 2)->default(0); $table->string('payment_method')->default('cash'); $table->timestamp('sold_at')->useCurrent()->index(); $table->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('sale_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->foreignId('medicine_batch_id')->constrained()->restrictOnDelete(); $table->unsignedInteger('quantity'); $table->decimal('unit_purchase_price', 12, 2); $table->decimal('unit_sale_price', 12, 2); $table->decimal('line_total', 12, 2); $table->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->restrictOnDelete(); $table->foreignId('medicine_batch_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('type'); $table->integer('quantity_change'); $table->nullableMorphs('reference'); $table->text('notes')->nullable(); $table->timestamps(); $table->index(['product_id', 'created_at']);
        });
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id(); $table->foreignId('sale_id')->constrained()->restrictOnDelete(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->decimal('total', 12, 2); $table->text('reason')->nullable(); $table->timestamp('returned_at')->useCurrent(); $table->timestamps();
        });
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete(); $table->foreignId('sale_item_id')->constrained()->restrictOnDelete(); $table->foreignId('medicine_batch_id')->constrained()->restrictOnDelete(); $table->unsignedInteger('quantity'); $table->decimal('line_total', 12, 2); $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->string('category'); $table->string('description'); $table->decimal('amount', 12, 2); $table->date('expense_date')->index(); $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['expenses', 'sale_return_items', 'sale_returns', 'stock_movements', 'sale_items', 'sales', 'cash_sessions', 'purchase_items', 'purchases', 'medicine_batches', 'products', 'customers'] as $table) { Schema::dropIfExists($table); }
    }
};
