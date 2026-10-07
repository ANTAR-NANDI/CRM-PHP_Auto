<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class ItemTransfer extends Model{protected $fillable=['transfer_no','transfer_date','transfer_type','from_store_id','to_store_id','remarks','total_amount','user_id'];protected function casts():array{return ['transfer_date'=>'date'];}public function items():HasMany{return $this->hasMany(ItemTransferItem::class);}}
