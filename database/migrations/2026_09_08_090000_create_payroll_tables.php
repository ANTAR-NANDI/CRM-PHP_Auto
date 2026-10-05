<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->date('period_start')->unique();
            $table->date('period_end');
            $table->string('status')->default('draft')->index();
            $table->decimal('total_net_salary', 14, 2)->default(0);
            $table->foreignId('account_voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('calendar_days');
            $table->decimal('paid_days', 6, 2)->default(0);
            $table->decimal('monthly_salary', 14, 2);
            $table->decimal('gross_salary', 14, 2);
            $table->decimal('deduction', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['payroll_id', 'user_id']);
        });

        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('payroll_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('account_voucher_id')->constrained()->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
    }
};
