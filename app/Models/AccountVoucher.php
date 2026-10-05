<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountVoucher extends Model
{
    protected $fillable = ['voucher_number', 'voucher_type', 'voucher_date', 'narration', 'amount', 'user_id'];

    protected function casts(): array
    {
        return ['voucher_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AccountVoucherEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
