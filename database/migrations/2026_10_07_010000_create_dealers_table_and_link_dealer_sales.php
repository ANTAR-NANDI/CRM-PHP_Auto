<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('dealer_sales', function (Blueprint $table) {
            $table->foreignId('dealer_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dealer_sales', fn (Blueprint $table) => $table->dropConstrainedForeignId('dealer_id'));
        Schema::dropIfExists('dealers');
    }
};
