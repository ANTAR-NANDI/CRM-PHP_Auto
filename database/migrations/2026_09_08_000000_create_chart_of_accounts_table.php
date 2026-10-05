<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense'])->index();
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_transactional')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['parent_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
