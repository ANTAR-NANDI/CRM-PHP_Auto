<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('designation')->nullable()->after('phone');
            $table->date('joining_date')->nullable()->after('designation');
            $table->decimal('salary', 12, 2)->default(0)->after('joining_date');
            $table->text('address')->nullable()->after('salary');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('present');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });

        DB::table('users')->select('id')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'employee_code' => 'EMP-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn([
            'employee_code', 'phone', 'designation', 'joining_date', 'salary', 'address',
        ]));
    }
};
