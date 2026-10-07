<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryIssue extends Model
{
    protected $fillable = ['issue_no', 'issue_date', 'issue_to_user_id', 'store_id', 'purpose', 'remarks', 'user_id'];
    protected function casts(): array { return ['issue_date' => 'date']; }
    public function items(): HasMany { return $this->hasMany(InventoryIssueItem::class); }
}
