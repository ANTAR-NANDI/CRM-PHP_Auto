<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('lead_status_histories', 'previous_lead_status_id')) {
            Schema::table('lead_status_histories', function (Blueprint $table) {
                $table->foreignId('previous_lead_status_id')->nullable()->after('lead_status_id')->constrained('crm_lead_statuses')->nullOnDelete();
            });
        }

        $validStatusIds = DB::table('crm_lead_statuses')->pluck('id')->flip();
        DB::table('lead_status_histories')->orderBy('lead_id')->orderBy('status_date')->orderBy('status_time')->orderBy('id')->get()->groupBy('lead_id')->each(function ($entries) use ($validStatusIds) {
            $previousStatusId = null;
            foreach ($entries as $entry) {
                DB::table('lead_status_histories')->where('id', $entry->id)->update(['previous_lead_status_id' => $previousStatusId]);
                $previousStatusId = $validStatusIds->has($entry->lead_status_id) ? $entry->lead_status_id : null;
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_status_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_lead_status_id');
        });
    }
};
