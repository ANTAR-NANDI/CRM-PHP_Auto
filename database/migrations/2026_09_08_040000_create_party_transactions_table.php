<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('transaction_type', ['customer_receive', 'supplier_payment'])->index();
            $table->foreignId('customer_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('cash_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('account_voucher_id')->constrained()->restrictOnDelete();
            $table->date('transaction_date')->index();
            $table->decimal('amount', 14, 2);
            $table->text('narration')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_transactions');
    }
};
