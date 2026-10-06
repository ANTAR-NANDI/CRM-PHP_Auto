<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Meeting extends Model { protected $fillable=['title','meeting_at','attendee_ids','agenda_ids','remarks','status','called_by_id']; protected function casts():array{return ['meeting_at'=>'datetime','attendee_ids'=>'array','agenda_ids'=>'array'];} public function calledBy():BelongsTo{return $this->belongsTo(User::class,'called_by_id');} }
