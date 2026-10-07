<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerSaleController extends Controller
{
    public function create(): View
    {
        return view('dealer-sales.create', [
            'nextSaleNo' => 'DLS-'.now()->format('ymdHis'),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()
                ->select(['id', 'name'])
                ->selectSub(
                    DB::table('medicine_batches')
                        ->select('sale_price')
                        ->whereColumn('medicine_batches.product_id', 'products.id')
                        ->orderByDesc('id')
                        ->limit(1),
                    'selling_price'
                )
                ->orderBy('name')
                ->get(),
            'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_date' => ['required', 'date'], 'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'customer_id' => ['nullable', 'exists:customers,id'], 'mode_of_sale' => ['required', 'string'],
            'tentative_delivery_date' => ['nullable', 'date'], 'delivery_point' => ['nullable', 'string', 'max:255'],
            'transfer_from_other' => ['nullable', 'boolean'], 'sales_person_id' => ['nullable', 'exists:users,id'],
            'product_manager_id' => ['nullable', 'exists:users,id'], 'remarks' => ['nullable', 'string'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'], 'payment_date' => ['nullable', 'date'],
            'payment_mode' => ['required', 'string'], 'booking_place' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.segment_id' => ['nullable', 'exists:crm_segments,id'], 'items.*.color' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:.01'], 'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.commission_per_unit' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['transfer_from_other'] = $request->boolean('transfer_from_other');

        DB::transaction(function () use ($data): void {
            $saleId = DB::table('dealer_sales')->insertGetId(collect($data)->except('items')->all() + ['sale_no' => 'DLS-'.now()->format('ymdHis'), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('dealer_sale_items')->insert(collect($data['items'])->map(fn (array $item) => $item + ['dealer_sale_id' => $saleId, 'amount' => $item['quantity'] * $item['price'], 'created_at' => now(), 'updated_at' => now()])->all());
        });

        return redirect()->route('dealer-sales.create')->with('success', 'Dealer sale saved successfully.');
    }
}
