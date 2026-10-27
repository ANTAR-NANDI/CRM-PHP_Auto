<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('received_at');
            $table->string('payment_mode');
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['dealer_sale_id', 'received_at']);
        });

        Schema::create('dealer_delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_sale_id')->constrained()->cascadeOnDelete();
            $table->string('challan_no')->unique();
            $table->date('delivered_at');
            $table->string('delivery_point')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('signed_file_path');
            $table->string('signed_file_name');
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['dealer_sale_id', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_delivery_challans');
        Schema::dropIfExists('dealer_collections');
    }
};
