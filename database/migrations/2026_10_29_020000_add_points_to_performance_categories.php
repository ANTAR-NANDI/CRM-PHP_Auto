<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('activity_types', 'point')) {
            Schema::table('activity_types', fn (Blueprint $table) => $table->unsignedInteger('point')->default(1)->after('name'));
        }
        if (! Schema::hasColumn('crm_lead_statuses', 'point')) {
            Schema::table('crm_lead_statuses', fn (Blueprint $table) => $table->unsignedInteger('point')->default(1)->after('name'));
        }

        foreach (['Desk Work' => 2, 'Digital' => 3, 'FB Page' => 1, 'Phone' => 2, 'Meeting' => 5, 'Online' => 2, 'SMS' => 1, 'Visit' => 5] as $name => $point) {
            DB::table('activity_types')->whereRaw('LOWER(name) = ?', [strtolower($name)])->update(['point' => $point]);
        }
        foreach (['Cold' => 2, 'Hot' => 5, 'Warm' => 3] as $name => $point) {
            DB::table('crm_lead_statuses')->whereRaw('LOWER(name) = ?', [strtolower($name)])->update(['point' => $point]);
        }
    }

    public function down(): void
    {
        Schema::table('activity_types', fn (Blueprint $table) => $table->dropColumn('point'));
        Schema::table('crm_lead_statuses', fn (Blueprint $table) => $table->dropColumn('point'));
    }
};
