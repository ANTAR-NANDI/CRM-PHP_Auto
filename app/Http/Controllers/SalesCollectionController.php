<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesCollectionController extends Controller
{
    public function create(): View
    {
        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->select('customer_vehicle_sales.*', 'customers.name as customer_name')
            ->latest('sale_date')->get();

        return view('sales-collections.create', [
            'sales' => $sales,
            'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'cheques' => DB::table('cheques')->where('status', 'in_hand')->latest()->get(['id', 'cheque_no', 'amount']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_vehicle_sale_id' => ['required', 'exists:customer_vehicle_sales,id'],
            'collection_type' => ['required', 'in:sales,advance'],
            'amount' => ['required', 'numeric', 'min:.01'],
            'received_at' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank,cheque,mobile_banking'],
            'bank_head' => ['nullable', 'string', 'max:255'],
            'cheque_id' => ['nullable', 'exists:cheques,id'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $sale = CustomerVehicleSale::findOrFail($data['customer_vehicle_sale_id']);
        $collected = DB::table('sales_collections')->where('customer_vehicle_sale_id', $sale->id)->sum('amount');
        $balance = max(0, (float) $sale->final_price - (float) $collected);
        if ((float) $data['amount'] > $balance && $data['collection_type'] === 'sales') {
            return back()->withInput()->withErrors(['amount' => 'Receive amount cannot exceed the remaining balance.']);
        }

        $chequeId = $data['cheque_id'] ?? null;
        unset($data['cheque_id']);
        $collectionId = DB::table('sales_collections')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
        if ($chequeId) DB::table('cheques')->where('id', $chequeId)->update(['sales_collection_id' => $collectionId, 'updated_at' => now()]);

        return redirect()->route('sales-collections.index')->with('success', 'Collection saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'search' => ['nullable', 'string', 'max:255']]);
        $collections = DB::table('sales_collections')
            ->leftJoin('customer_vehicle_sales as sale', 'sales_collections.customer_vehicle_sale_id', '=', 'sale.id')
            ->leftJoin('customers as customer', 'sale.customer_id', '=', 'customer.id')
            ->select('sales_collections.*', 'sale.sale_no', 'sale.sale_date', 'sale.dealer', 'customer.name as customer_name', 'customer.phone as customer_phone')
            ->when($filters['segment'] ?? null, fn ($query, $value) => $query->where('sale.segment_id', $value))
            ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('sales_collections.received_at', '>=', $value))
            ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('sales_collections.received_at', '<=', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('sale.sale_no', 'like', "%{$value}%")->orWhere('customer.name', 'like', "%{$value}%")->orWhere('customer.phone', 'like', "%{$value}%")))
            ->latest('sales_collections.received_at')->get();
        return view('sales-collections.index', ['collections' => $collections, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }
}
