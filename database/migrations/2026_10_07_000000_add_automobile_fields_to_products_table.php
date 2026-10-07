<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('segment_id')->nullable()->after('name')->constrained('crm_segments')->nullOnDelete();
            $table->text('description')->nullable()->after('category');
            $table->text('technical_specification')->nullable()->after('description');
            $table->decimal('unit_price', 14, 2)->default(0)->after('technical_specification');
            $table->enum('commission_type', ['percent', 'fixed'])->default('percent')->after('unit_price');
            $table->decimal('unit_commission', 14, 2)->default(0)->after('commission_type');
            $table->index(['segment_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['segment_id', 'is_active']);
            $table->dropConstrainedForeignId('segment_id');
            $table->dropColumn(['description', 'technical_specification', 'unit_price', 'commission_type', 'unit_commission']);
        });
    }
};
