<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach ([
            ['name' => 'New Lead', 'badge_color' => '#64748b'],
            ['name' => 'Contacted', 'badge_color' => '#2563eb'],
            ['name' => 'Showroom Visit', 'badge_color' => '#7c3aed'],
            ['name' => 'Test Drive', 'badge_color' => '#d97706'],
            ['name' => 'Negotiation', 'badge_color' => '#ea580c'],
            ['name' => 'Won', 'badge_color' => '#059669'],
            ['name' => 'Lost', 'badge_color' => '#64748b'],
        ] as $status) {
            DB::table('crm_lead_statuses')->updateOrInsert(['name' => $status['name']], $status + ['is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('crm_lead_statuses')->whereIn('name', ['New Lead', 'Contacted', 'Showroom Visit', 'Test Drive', 'Negotiation'])->delete();
    }
};
