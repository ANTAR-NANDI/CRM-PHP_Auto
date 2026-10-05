<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model
{
    protected $fillable = [
        'product_id', 'supplier_id', 'batch_number', 'expires_on', 'quantity_received',
        'quantity_available', 'purchase_price', 'sale_price', 'strip_sale_price',
    ];

    protected function casts(): array
    {
        return [
            'expires_on' => 'date',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'strip_sale_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
