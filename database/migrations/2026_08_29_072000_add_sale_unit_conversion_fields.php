<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('sale_unit')->default('piece')->after('quantity');
            $table->unsignedInteger('units_per_sale_unit')->default(1)->after('sale_unit');
            $table->unsignedInteger('stock_quantity')->default(0)->after('units_per_sale_unit');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', fn (Blueprint $table) => $table->dropColumn([
            'sale_unit', 'units_per_sale_unit', 'stock_quantity',
        ]));
    }
};
