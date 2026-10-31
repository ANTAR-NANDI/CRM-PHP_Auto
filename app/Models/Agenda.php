<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agenda extends Model
{
    protected $fillable = ['segment_id', 'title', 'pre_event_plan_id', 'remarks', 'status', 'start_date', 'end_date', 'responsible_user_id'];

    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date']; }

    public function plan(): BelongsTo { return $this->belongsTo(PreEventPlan::class, 'pre_event_plan_id'); }
    public function responsible(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
    public function updates(): HasMany { return $this->hasMany(AgendaUpdate::class); }
    public function latestUpdate(): HasOne { return $this->hasOne(AgendaUpdate::class)->latestOfMany(); }
}
