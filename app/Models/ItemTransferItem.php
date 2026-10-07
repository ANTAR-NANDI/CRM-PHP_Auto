<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;class ItemTransferItem extends Model{protected $fillable=['item_transfer_id','product_id','from_position_id','to_position_id','quantity','unit_cost','amount'];}
