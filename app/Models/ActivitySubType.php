<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySubType extends Model
{
    protected $fillable = ['activity_type_id', 'name', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function type(): BelongsTo { return $this->belongsTo(ActivityType::class, 'activity_type_id'); }
}
