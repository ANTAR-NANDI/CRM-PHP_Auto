<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSavedOrder extends Model
{
    protected $fillable = ['order_number', 'user_id', 'customer_id', 'customer_type', 'items', 'discount', 'paid', 'payment_method', 'status', 'note', 'sale_id', 'completed_at'];

    protected function casts(): array
    {
        return ['items' => 'array', 'discount' => 'decimal:2', 'paid' => 'decimal:2', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
}
