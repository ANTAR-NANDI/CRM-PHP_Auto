<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['crm_organizations', 'crm_segments', 'crm_lead_statuses', 'crm_pipelines', 'crm_sources', 'crm_colors'] as $table) {
            Schema::create($table, function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->boolean('is_active')->default(true); $table->timestamps(); });
        }
        Schema::table('crm_lead_statuses', fn (Blueprint $table) => $table->string('badge_color')->default('#b89532')->after('name'));
        Schema::table('crm_pipelines', fn (Blueprint $table) => $table->string('code')->nullable()->unique()->after('name'));
        Schema::table('crm_colors', fn (Blueprint $table) => $table->string('hex_code')->nullable()->after('name'));
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('phone')->nullable()->index(); $table->string('alternate_phone')->nullable(); $table->string('email')->nullable(); $table->text('address')->nullable(); $table->string('job_title')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained('crm_organizations')->nullOnDelete(); $table->foreignId('segment_id')->nullable()->constrained('crm_segments')->nullOnDelete(); $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('color_id')->nullable()->constrained('crm_colors')->nullOnDelete();
            $table->foreignId('lead_status_id')->constrained('crm_lead_statuses')->restrictOnDelete(); $table->foreignId('pipeline_id')->nullable()->constrained('crm_pipelines')->nullOnDelete(); $table->foreignId('owner_id')->constrained('users')->restrictOnDelete(); $table->foreignId('activity_type_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('source_id')->nullable()->constrained('crm_sources')->nullOnDelete();
            $table->date('lead_date'); $table->string('source_detail')->nullable(); $table->text('remarks')->nullable(); $table->string('business_card_path')->nullable(); $table->timestamps();
            $table->index(['lead_status_id', 'pipeline_id', 'lead_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('leads'); foreach (['crm_colors', 'crm_sources', 'crm_pipelines', 'crm_lead_statuses', 'crm_segments', 'crm_organizations'] as $table) Schema::dropIfExists($table); }
};
