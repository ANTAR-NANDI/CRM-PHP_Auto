<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('sales_target_items', fn (Blueprint $table) => $table->foreignId('item_segment_id')->nullable()->after('sales_target_id')->constrained('crm_segments')->nullOnDelete()); } public function down(): void { Schema::table('sales_target_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('item_segment_id')); } };
