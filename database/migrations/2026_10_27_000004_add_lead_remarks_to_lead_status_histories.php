<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lead_status_histories', function (Blueprint $table) {
            $table->text('lead_remarks')->nullable()->after('note');
        });

        DB::table('lead_status_histories as history')
            ->join('leads as lead', 'lead.id', '=', 'history.lead_id')
            ->update(['history.lead_remarks' => DB::raw('lead.remarks')]);
    }

    public function down(): void
    {
        Schema::table('lead_status_histories', function (Blueprint $table) {
            $table->dropColumn('lead_remarks');
        });
    }
};
