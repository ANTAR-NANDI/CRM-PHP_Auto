<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    protected $fillable = ['payment_number', 'payroll_item_id', 'cash_account_id', 'account_voucher_id', 'payment_date', 'amount', 'notes', 'user_id'];
    protected function casts(): array { return ['payment_date' => 'date', 'amount' => 'decimal:2']; }
    public function item(): BelongsTo { return $this->belongsTo(PayrollItem::class, 'payroll_item_id'); }
    public function cashAccount(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'cash_account_id'); }
    public function voucher(): BelongsTo { return $this->belongsTo(AccountVoucher::class, 'account_voucher_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
