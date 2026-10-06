<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreEventPlan extends Model
{
    protected $fillable = ['title', 'tentative_date', 'description', 'remarks', 'supervisor_id', 'status'];
    protected function casts(): array { return ['tentative_date' => 'date']; }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
}
