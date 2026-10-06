<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Agenda extends Model { protected $fillable=['segment_id','title','pre_event_plan_id','remarks','status','start_date','end_date','responsible_user_id']; protected function casts():array{return ['start_date'=>'date','end_date'=>'date'];} public function plan():BelongsTo{return $this->belongsTo(PreEventPlan::class,'pre_event_plan_id');} public function responsible():BelongsTo{return $this->belongsTo(User::class,'responsible_user_id');} }
