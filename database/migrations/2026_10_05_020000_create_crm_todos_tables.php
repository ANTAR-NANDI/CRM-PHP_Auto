<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('todo_types', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->boolean('is_active')->default(true); $table->timestamps(); });
        Schema::create('todos', function (Blueprint $table) {
            $table->id(); $table->foreignId('todo_type_id')->constrained()->restrictOnDelete(); $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->string('subject_type')->default('lead'); $table->unsignedBigInteger('subject_id')->nullable(); $table->string('task_with')->nullable(); $table->timestamp('due_at'); $table->string('priority')->default('medium'); $table->unsignedInteger('remind_before_minutes')->default(0); $table->text('note')->nullable(); $table->string('status')->default('pending'); $table->timestamp('completed_at')->nullable(); $table->timestamps();
            $table->index(['status', 'due_at']); $table->index(['subject_type', 'subject_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('todos'); Schema::dropIfExists('todo_types'); }
};
