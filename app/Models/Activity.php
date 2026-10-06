<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['activity_type_id', 'activity_sub_type_id', 'user_id', 'subject_type', 'subject_id', 'activity_with', 'from_at', 'to_at', 'remarks', 'keep_todo', 'priority', 'attachment_path'];

    protected function casts(): array { return ['from_at' => 'datetime', 'to_at' => 'datetime', 'keep_todo' => 'boolean']; }

    public function type(): BelongsTo { return $this->belongsTo(ActivityType::class, 'activity_type_id'); }
    public function subType(): BelongsTo { return $this->belongsTo(ActivitySubType::class, 'activity_sub_type_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
