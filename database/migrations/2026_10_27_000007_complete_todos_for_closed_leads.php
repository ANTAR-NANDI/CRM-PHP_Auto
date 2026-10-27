<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $closedLeadIds = DB::table('leads')
            ->join('crm_lead_statuses', 'leads.lead_status_id', '=', 'crm_lead_statuses.id')
            ->whereIn(DB::raw('LOWER(crm_lead_statuses.name)'), ['won', 'lost'])
            ->pluck('leads.id');

        DB::table('todos')->where('subject_type', 'lead')->whereIn('subject_id', $closedLeadIds)->where('status', 'pending')->update([
            'status' => 'completed', 'completed_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void {}
};
