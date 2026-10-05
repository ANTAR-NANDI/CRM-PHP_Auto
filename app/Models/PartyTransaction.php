<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyTransaction extends Model
{
    protected $fillable = ['transaction_number', 'transaction_type', 'customer_id', 'supplier_id', 'cash_account_id', 'account_voucher_id', 'transaction_date', 'amount', 'narration', 'user_id'];

    protected function casts(): array
    {
        return ['transaction_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function cashAccount(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'cash_account_id'); }
    public function voucher(): BelongsTo { return $this->belongsTo(AccountVoucher::class, 'account_voucher_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
