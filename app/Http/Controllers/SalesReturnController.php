<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesReturnController
{
    public function create(): View
    {
        return view('sales-returns.create', ['sales' => CustomerVehicleSale::latest()->get(['id', 'sale_no', 'segment_id']), 'segments' => DB::table('crm_segments')->where('is_active', true)->get(), 'products' => Product::orderBy('name')->get(['id', 'name'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['customer_vehicle_sale_id' => ['nullable', 'exists:customer_vehicle_sales,id'], 'segment_id' => ['nullable', 'exists:crm_segments,id'], 'return_date' => ['required', 'date'], 'engine_no' => ['nullable', 'string', 'max:100'], 'chassis_no' => ['nullable', 'string', 'max:100'], 'amount' => ['required', 'numeric', 'min:0'], 'remarks' => ['nullable', 'string', 'max:5000']]);
        DB::table('sales_returns')->insert($data + ['return_no' => 'SRT-'.now()->format('ymdHis'), 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('sales-returns.index')->with('success', 'Sales return saved successfully.');
    }

    public function index(Request $request): View
    {
        $f = $request->validate(['segment' => ['nullable', 'integer'], 'dealer' => ['nullable', 'string'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'search' => ['nullable', 'string']]);
        $q = DB::table('sales_returns')->leftJoin('crm_segments as segment', 'sales_returns.segment_id', '=', 'segment.id')->leftJoin('customer_vehicle_sales as sale', 'sales_returns.customer_vehicle_sale_id', '=', 'sale.id')->select('sales_returns.*', 'segment.name as segment_name', 'sale.dealer');
        if ($f['segment'] ?? null) $q->where('sales_returns.segment_id', $f['segment']); if ($f['dealer'] ?? null) $q->where('sale.dealer', $f['dealer']); if ($f['from'] ?? null) $q->whereDate('return_date', '>=', $f['from']); if ($f['to'] ?? null) $q->whereDate('return_date', '<=', $f['to']); if ($f['search'] ?? null) $q->where(fn ($x) => $x->where('return_no', 'like', '%'.$f['search'].'%')->orWhere('engine_no', 'like', '%'.$f['search'].'%')->orWhere('chassis_no', 'like', '%'.$f['search'].'%'));
        return view('sales-returns.index', ['returns' => $q->latest('return_date')->paginate(25), 'filters' => $f, 'segments' => DB::table('crm_segments')->where('is_active', true)->get(), 'dealers' => DB::table('customer_vehicle_sales')->whereNotNull('dealer')->distinct()->pluck('dealer')]);
    }
}
