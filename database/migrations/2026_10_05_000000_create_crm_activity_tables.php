<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_sub_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['activity_type_id', 'name']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_sub_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('subject_type')->default('customer');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('activity_with')->nullable();
            $table->timestamp('from_at');
            $table->timestamp('to_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('keep_todo')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['activity_type_id', 'from_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_sub_types');
        Schema::dropIfExists('activity_types');
    }
};
