<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lead_status_histories', function (Blueprint $table) {
            $table->time('status_time')->nullable()->after('status_date');
        });

        DB::table('lead_status_histories')->orderBy('id')->eachById(function ($history) {
            DB::table('lead_status_histories')->where('id', $history->id)->update([
                'status_time' => $history->created_at ? date('H:i:s', strtotime($history->created_at)) : null,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('lead_status_histories', function (Blueprint $table) {
            $table->dropColumn('status_time');
        });
    }
};
