<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_status_id')->constrained('crm_lead_statuses')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('status_date');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'status_date']);
        });

        DB::table('leads')->orderBy('id')->eachById(function ($lead) {
            DB::table('lead_status_histories')->insert([
                'lead_id' => $lead->id, 'lead_status_id' => $lead->lead_status_id,
                'changed_by' => $lead->created_by ?? $lead->owner_id, 'status_date' => $lead->lead_date,
                'note' => 'Initial lead status', 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function down(): void { Schema::dropIfExists('lead_status_histories'); }
};
