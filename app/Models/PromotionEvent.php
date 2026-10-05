<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class PromotionEvent extends Model { protected $fillable=['segment_id','event_type_id','title','supervisor_id','attendee_ids','attachment_path','start_date','end_date','status','remarks']; protected function casts():array{return ['attendee_ids'=>'array','start_date'=>'date','end_date'=>'date'];} }
