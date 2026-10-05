<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = ['period_start', 'period_end', 'status', 'total_net_salary', 'account_voucher_id', 'created_by', 'posted_by', 'posted_at'];
    protected function casts(): array { return ['period_start' => 'date', 'period_end' => 'date', 'posted_at' => 'datetime', 'total_net_salary' => 'decimal:2']; }
    public function items(): HasMany { return $this->hasMany(PayrollItem::class); }
    public function voucher(): BelongsTo { return $this->belongsTo(AccountVoucher::class, 'account_voucher_id'); }
}
