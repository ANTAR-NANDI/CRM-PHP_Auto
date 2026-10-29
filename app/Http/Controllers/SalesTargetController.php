<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesTargetController extends Controller
{
    public function create(): View
    {
        return view('sales-targets.create', ['segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'salesExecutives' => User::query()->where('is_active', true)->where(fn ($query) => $query->whereRaw('LOWER(COALESCE(designation, \'\')) LIKE ?', ['%sales executive%'])->orWhere('role', 'salesperson')->orWhereHas('roles', fn ($roles) => $roles->where('name', 'sales-executive')))->orderBy('name')->get(['id', 'name']), 'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'segment_id', 'unit_price'])]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'month' => ['nullable', 'integer', 'between:1,12'], 'year' => ['nullable', 'integer', 'between:2020,2100']]);
        $targets = SalesTarget::query()->leftJoin('crm_segments as segment', 'sales_targets.segment_id', '=', 'segment.id')->leftJoin('users as executive', 'sales_targets.sales_executive_id', '=', 'executive.id')->select('sales_targets.*', 'segment.name as segment_name', 'executive.name as sales_executive_name')->when($filters['segment'] ?? null, fn ($q, $x) => $q->where('segment_id', $x))->when($filters['month'] ?? null, fn ($q, $x) => $q->where('target_month', $x))->when($filters['year'] ?? null, fn ($q, $x) => $q->where('target_year', $x))->latest()->get();

        return view('sales-targets.index', ['targets' => $targets, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get()]);
    }

    public function performance(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'month' => ['nullable', 'integer', 'between:1,12'], 'year' => ['nullable', 'integer', 'between:2020,2100']]);
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $targetItems = DB::table('sales_target_items as item')->join('sales_targets as target', 'item.sales_target_id', '=', 'target.id')->leftJoin('crm_segments as segment', 'target.segment_id', '=', 'segment.id')->leftJoin('products as product', 'item.product_id', '=', 'product.id')->leftJoin('suppliers as supplier', 'item.supplier_id', '=', 'supplier.id')->select('item.id', 'item.product_id', 'item.target_quantity', 'item.target_value', 'target.segment_id', 'segment.name as segment_name', 'product.name as product_name', 'supplier.name as supplier_name')->where('target.target_month', $month)->where('target.target_year', $year)->when($filters['segment'] ?? null, fn ($query, $value) => $query->where('target.segment_id', $value))->orderBy('segment.name')->orderBy('product.name')->get();
        $actualSalesRows = DB::table('customer_vehicle_sales as sale')->leftJoin('products as product', 'sale.product_id', '=', 'product.id')->leftJoin('crm_segments as segment', 'sale.segment_id', '=', 'segment.id')->selectRaw("sale.product_id, sale.segment_id, COALESCE(product.name, 'Unspecified product') as product_name, segment.name as segment_name, SUM(sale.quantity) as actual_quantity, SUM(sale.final_price) as actual_value")->whereYear('sale.sale_date', $year)->whereMonth('sale.sale_date', $month)->when($filters['segment'] ?? null, fn ($query, $value) => $query->where('sale.segment_id', $value))->groupBy('sale.product_id', 'sale.segment_id', 'product.name', 'segment.name')->get();
        $actualSales = $actualSalesRows->groupBy('product_id');
        $performance = $targetItems->map(function ($item) use ($actualSales) {
            $matchingSales = $actualSales->get($item->product_id, collect());
            if ($item->segment_id) $matchingSales = $matchingSales->where('segment_id', $item->segment_id);
            $actualQuantity = (float) $matchingSales->sum('actual_quantity');
            $actualValue = (float) $matchingSales->sum('actual_value');
            $quantityMissed = max(0, (float) $item->target_quantity - $actualQuantity);
            $valueMissed = max(0, (float) $item->target_value - $actualValue);

            return (object) [...(array) $item, 'actual_quantity' => $actualQuantity, 'actual_value' => $actualValue, 'quantity_missed' => $quantityMissed, 'value_missed' => $valueMissed, 'achieved' => $quantityMissed <= 0 && $valueMissed <= 0, 'target_set' => true];
        });
        $salesWithoutTargets = $actualSalesRows->filter(fn ($sale) => ! $targetItems->contains(fn ($target) => (int) $target->product_id === (int) $sale->product_id && (! $target->segment_id || (int) $target->segment_id === (int) $sale->segment_id)))->map(fn ($sale) => (object) ['product_name' => $sale->product_name, 'segment_name' => $sale->segment_name, 'supplier_name' => null, 'target_quantity' => 0, 'target_value' => 0, 'actual_quantity' => (float) $sale->actual_quantity, 'actual_value' => (float) $sale->actual_value, 'quantity_missed' => 0, 'value_missed' => 0, 'achieved' => false, 'target_set' => false]);
        $performance = $performance->concat($salesWithoutTargets);

        return view('sales-targets.performance', ['performance' => $performance, 'filters' => $filters, 'month' => $month, 'year' => $year, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['segment_id' => ['required', 'exists:crm_segments,id'], 'target_month' => ['required', 'integer', 'between:1,12'], 'target_year' => ['required', 'integer', 'between:2020,2100'], 'remarks' => ['nullable', 'string', 'max:5000'], 'items' => ['required', 'array', 'min:1'], 'items.*.sales_executive_id' => ['required', 'exists:users,id'], 'items.*.product_id' => ['required', 'exists:products,id'], 'items.*.target_quantity' => ['required', 'numeric', 'min:0.01'], 'items.*.price' => ['nullable', 'numeric', 'min:0'], 'items.*.target_value' => ['nullable', 'numeric', 'min:0']]);
        DB::transaction(function () use ($data): void {
            collect($data['items'])->map(fn ($x) => $x + ['price' => $x['price'] ?? 0, 'target_value' => $x['target_value'] ?? (($x['target_quantity'] ?? 0) * ($x['price'] ?? 0))])->groupBy('sales_executive_id')->each(function ($items, $salesExecutiveId) use ($data): void {
                $target = SalesTarget::create(collect($data)->except('items', 'sales_executive_id')->all() + ['sales_executive_id' => $salesExecutiveId, 'total_quantity' => $items->sum('target_quantity'), 'total_value' => $items->sum('target_value')]);
                $target->items()->createMany($items->map(fn ($item) => collect($item)->except('sales_executive_id')->all())->all());
            });
        });

        return redirect()->route('sales-targets.index')->with('success', 'Sales target saved successfully.');
    }
}
