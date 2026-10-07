<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class GoodsReceiptItem extends Model { protected $fillable=['goods_receipt_id','product_id','store_position_id','quantity','rate','amount','landed_cost']; }
