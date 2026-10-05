<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('pieces_per_strip')->default(1)->after('unit');
            $table->boolean('sell_by_piece')->default(true)->after('pieces_per_strip');
            $table->boolean('sell_by_strip')->default(false)->after('sell_by_piece');
        });

        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->decimal('strip_sale_price', 12, 2)->nullable()->after('sale_price');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('purchase_unit')->default('piece')->after('quantity');
            $table->unsignedInteger('units_per_purchase_unit')->default(1)->after('purchase_unit');
            $table->unsignedInteger('stock_quantity')->default(0)->after('units_per_purchase_unit');
            $table->decimal('strip_sale_price', 12, 2)->nullable()->after('sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_unit', 'units_per_purchase_unit', 'stock_quantity', 'strip_sale_price']);
        });
        Schema::table('medicine_batches', fn (Blueprint $table) => $table->dropColumn('strip_sale_price'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['pieces_per_strip', 'sell_by_piece', 'sell_by_strip']));
    }
};
