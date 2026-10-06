<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class SalesTargetItem extends Model { protected $fillable=['sales_target_id','supplier_id','product_id','target_quantity','price','target_value']; }
