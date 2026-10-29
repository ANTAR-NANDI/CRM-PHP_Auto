<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['event_activities', 'event_expenses'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->id(); $blueprint->foreignId('promotion_event_id')->constrained()->cascadeOnDelete(); $blueprint->string('title'); $blueprint->date('activity_date')->nullable(); $blueprint->decimal('amount', 14, 2)->nullable(); $blueprint->text('remarks')->nullable(); $blueprint->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('event_expenses'); Schema::dropIfExists('event_activities'); }
};
