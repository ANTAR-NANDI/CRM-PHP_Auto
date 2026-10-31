<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('crm_pipelines', 'color')) {
            Schema::table('crm_pipelines', fn (Blueprint $table) => $table->string('color', 7)->default('#64748b')->after('code'));
        }

        if (! Schema::hasColumn('crm_pipelines', 'sort_index')) {
            Schema::table('crm_pipelines', fn (Blueprint $table) => $table->unsignedInteger('sort_index')->default(0)->after('color'));
        }
    }

    public function down(): void
    {
        $columns = array_filter(['color', 'sort_index'], fn (string $column) => Schema::hasColumn('crm_pipelines', $column));
        if ($columns) {
            Schema::table('crm_pipelines', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
