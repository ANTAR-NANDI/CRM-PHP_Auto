<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryIssueItem extends Model
{
    protected $fillable = ['inventory_issue_id', 'product_id', 'store_position_id', 'balance_quantity', 'quantity', 'chassis_no'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
