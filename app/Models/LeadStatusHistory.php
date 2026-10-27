<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends Model
{
    protected $fillable = ['lead_id', 'lead_status_id', 'previous_lead_status_id', 'changed_by', 'status_date', 'status_time', 'note', 'lead_remarks'];
    protected function casts(): array { return ['status_date' => 'date']; }
    public function status(): BelongsTo { return $this->belongsTo(LeadStatus::class, 'lead_status_id'); }
    public function previousStatus(): BelongsTo { return $this->belongsTo(LeadStatus::class, 'previous_lead_status_id'); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
