<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->unique()->constrained('permissions')->cascadeOnDelete();
            $table->string('module_name', 100);
            $table->string('submodule_name', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_definitions');
    }
};
