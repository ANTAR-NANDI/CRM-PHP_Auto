<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany; class EventBudget extends Model { protected $fillable=['promotion_event_id','remarks','total_amount']; public function items():HasMany{return $this->hasMany(EventBudgetItem::class);} }
