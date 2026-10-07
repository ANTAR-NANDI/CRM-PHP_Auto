<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOpeningStock extends Model
{
    protected $fillable = ['product_id', 'store_id', 'store_position_id', 'quantity', 'opening_date'];

    protected function casts(): array { return ['opening_date' => 'date', 'quantity' => 'decimal:2']; }
}
