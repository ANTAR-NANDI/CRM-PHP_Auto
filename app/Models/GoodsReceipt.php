<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class GoodsReceipt extends Model { protected $fillable=['mrr_no','mrr_date','payment_mode','cash_head','purchase_type','currency','exchange_rate','store_id','supplier_id','po_no','remarks','total_amount','user_id']; protected function casts():array{return ['mrr_date'=>'date'];} public function items():HasMany{return $this->hasMany(GoodsReceiptItem::class);} }
