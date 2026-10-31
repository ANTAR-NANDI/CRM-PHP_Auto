<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{
    protected $fillable = ['customer_vehicle_sale_id', 'do_no', 'do_date', 'attention_user_id', 'delivery_place', 'remarks', 'engine_no', 'chassis_no', 'note'];
    protected function casts(): array { return ['do_date' => 'date']; }
    public function sale(): BelongsTo { return $this->belongsTo(CustomerVehicleSale::class, 'customer_vehicle_sale_id'); }
    public function attention(): BelongsTo { return $this->belongsTo(User::class, 'attention_user_id'); }
}
