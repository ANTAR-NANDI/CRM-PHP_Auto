<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    protected $fillable = ['payroll_id', 'user_id', 'calendar_days', 'paid_days', 'monthly_salary', 'gross_salary', 'deduction', 'net_salary', 'paid_amount'];
    protected function casts(): array { return ['paid_days' => 'decimal:2', 'monthly_salary' => 'decimal:2', 'gross_salary' => 'decimal:2', 'deduction' => 'decimal:2', 'net_salary' => 'decimal:2', 'paid_amount' => 'decimal:2']; }
    public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
    public function employee(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function payments(): HasMany { return $this->hasMany(SalaryPayment::class); }
    public function getDueAttribute(): float { return max(0, (float) $this->net_salary - (float) $this->paid_amount); }
}
