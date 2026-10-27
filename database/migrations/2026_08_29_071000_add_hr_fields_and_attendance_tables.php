<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing('users');
        Schema::table('users', function (Blueprint $table) use ($columns) {
            if (! in_array('employee_code', $columns, true)) $table->string('employee_code')->nullable()->unique()->after('id');
            if (! in_array('phone', $columns, true)) $table->string('phone', 50)->nullable()->after('email');
            if (! in_array('designation', $columns, true)) $table->string('designation')->nullable()->after('phone');
            if (! in_array('joining_date', $columns, true)) $table->date('joining_date')->nullable()->after('designation');
            if (! in_array('salary', $columns, true)) $table->decimal('salary', 12, 2)->default(0)->after('joining_date');
            if (! in_array('address', $columns, true)) $table->text('address')->nullable()->after('salary');
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
