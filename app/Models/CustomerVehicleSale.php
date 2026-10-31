<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerVehicleSale extends Model
{
    protected $fillable = ['sale_no', 'sale_date', 'customer_id', 'dealer', 'mode_of_sale', 'delivery_point', 'tentative_delivery_date', 'sales_person_id', 'product_manager_id', 'remarks', 'segment_id', 'product_id', 'color', 'quantity', 'body_type', 'body_builders_info', 'tyre_size', 'seat_capacity', 'registration_type', 'registration_place', 'vehicle_tracker', 'offered_price', 'unit_price', 'discount_amount', 'final_price', 'paid_amount', 'payment_date', 'payment_mode', 'delivery_image_path', 'account_voucher_id', 'account_clearance_status', 'account_cleared_by', 'account_cleared_at', 'account_clearance_note'];

    protected function casts(): array
    {
        return ['sale_date' => 'date', 'tentative_delivery_date' => 'date', 'payment_date' => 'date', 'account_cleared_at' => 'datetime', 'vehicle_tracker' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
