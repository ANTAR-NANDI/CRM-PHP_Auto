<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    protected $fillable = ['requisition_no', 'requisition_date', 'remarks', 'user_id'];
    protected function casts(): array { return ['requisition_date' => 'date']; }
    public function items(): HasMany { return $this->hasMany(RequisitionItem::class); }
}
