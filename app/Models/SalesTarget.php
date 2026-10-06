<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class SalesTarget extends Model { protected $fillable=['segment_id','target_month','target_year','remarks','total_quantity','total_value']; public function items():HasMany{return $this->hasMany(SalesTargetItem::class);} }
