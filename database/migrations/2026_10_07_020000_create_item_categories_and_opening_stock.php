<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->foreignId('parent_id')->nullable()->constrained('item_categories')->nullOnDelete(); $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('item_category_id')->nullable()->after('name')->constrained('item_categories')->nullOnDelete();
            $table->string('part_no')->nullable()->after('name')->unique();
        });
        Schema::create('product_opening_stocks', function (Blueprint $table) {
            $table->id(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->foreignId('store_id')->constrained()->restrictOnDelete(); $table->foreignId('store_position_id')->nullable()->constrained()->nullOnDelete(); $table->decimal('quantity', 14, 2)->default(0); $table->date('opening_date'); $table->timestamps(); $table->unique(['product_id', 'store_id', 'store_position_id'], 'product_opening_stock_location_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('product_opening_stocks');
        Schema::table('products', function (Blueprint $table) { $table->dropConstrainedForeignId('item_category_id'); $table->dropUnique(['part_no']); $table->dropColumn('part_no'); });
        Schema::dropIfExists('item_categories');
    }
};
