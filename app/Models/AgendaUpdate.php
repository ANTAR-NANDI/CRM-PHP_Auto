<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaUpdate extends Model
{
    protected $fillable = ['agenda_id', 'update_note', 'status', 'user_id'];

    public function agenda(): BelongsTo { return $this->belongsTo(Agenda::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
