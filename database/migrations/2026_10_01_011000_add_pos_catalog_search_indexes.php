<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'products_pos_active_name_index');
            $table->index(['is_active', 'category'], 'products_pos_active_category_index');
        });

        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->index(['product_id', 'quantity_available'], 'medicine_batches_pos_stock_index');
        });
    }

    public function down(): void
    {
        Schema::table('medicine_batches', fn (Blueprint $table) => $table->dropIndex('medicine_batches_pos_stock_index'));
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_pos_active_name_index');
            $table->dropIndex('products_pos_active_category_index');
        });
    }
};
