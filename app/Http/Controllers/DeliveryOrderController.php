<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use App\Models\DeliveryOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    public function create(CustomerVehicleSale $customerSale): View
    {
        abort_if(DeliveryOrder::where('customer_vehicle_sale_id', $customerSale->id)->exists(), 409, 'A delivery order has already been generated for this sale.');
        $customerSale->load(['customer', 'product']);
        return view('delivery-orders.create', ['sale' => $customerSale, 'nextDoNo' => 'DO-'.now()->format('ymd').'-'.str_pad((string) (DeliveryOrder::count() + 1), 4, '0', STR_PAD_LEFT), 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'showrooms' => Store::where('is_active', true)->where('store_type', 'showroom')->orderBy('name')->get(['id', 'name', 'code'])]);
    }

    public function store(Request $request, CustomerVehicleSale $customerSale): RedirectResponse
    {
        $data = $request->validate(['do_date' => ['required', 'date'], 'attention_user_id' => ['nullable', 'exists:users,id'], 'delivery_place' => ['nullable', 'string', 'max:255'], 'remarks' => ['nullable', 'string', 'max:5000'], 'engine_no' => ['nullable', 'string', 'max:100'], 'chassis_no' => ['nullable', 'string', 'max:100'], 'note' => ['nullable', 'string', 'max:5000']]);
        DeliveryOrder::create($data + ['customer_vehicle_sale_id' => $customerSale->id, 'do_no' => 'DO-'.now()->format('ymd').'-'.str_pad((string) (DeliveryOrder::count() + 1), 4, '0', STR_PAD_LEFT)]);
        return redirect()->route('customer-sales.index')->with('success', 'Delivery order generated successfully.');
    }
}
