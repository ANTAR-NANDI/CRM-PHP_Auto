<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pre_event_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('tentative_date')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('pre_event_plans'); }
};
