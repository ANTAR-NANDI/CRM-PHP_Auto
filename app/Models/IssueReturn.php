<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class IssueReturn extends Model{protected $fillable=['return_no','return_date','returned_by_user_id','store_id','remarks','user_id'];protected function casts():array{return ['return_date'=>'date'];}public function items():HasMany{return $this->hasMany(IssueReturnItem::class);}}
