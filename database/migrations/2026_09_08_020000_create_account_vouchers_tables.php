<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->enum('voucher_type', ['debit', 'credit'])->index();
            $table->date('voucher_date')->index();
            $table->text('narration')->nullable();
            $table->decimal('amount', 14, 2);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('account_voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->constrained()->restrictOnDelete();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['chart_of_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_voucher_entries');
        Schema::dropIfExists('account_vouchers');
    }
};
