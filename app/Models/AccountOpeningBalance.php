<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountOpeningBalance extends Model
{
    protected $fillable = ['chart_of_account_id', 'opening_date', 'debit', 'credit', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['opening_date' => 'date', 'debit' => 'decimal:2', 'credit' => 'decimal:2'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
