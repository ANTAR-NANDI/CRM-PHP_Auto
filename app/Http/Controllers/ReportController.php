<?php

namespace App\Http\Controllers;

use App\Models\MedicineBatch;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $monthStart = today()->startOfMonth();

        return view('admin.reports.index', [
            'monthlyPurchases' => (float) Purchase::query()->whereDate('purchased_at', '>=', $monthStart)->sum('total'),
            'monthlySales' => (float) Sale::query()->whereDate('sold_at', '>=', $monthStart)->sum('total'),
            'stockValue' => (float) MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) AS value')->value('value'),
            'neededProducts' => $this->lowStockQuery()->count(),
        ]);
    }

    public function purchases(Request $request): View
    {
        [$from, $to] = $this->dates($request);
        $supplierId = $request->integer('supplier_id') ?: null;
        $query = Purchase::query()->with(['supplier', 'user'])->withCount('items')
            ->whereBetween('purchased_at', [$from, $to])
            ->when($supplierId, fn (Builder $builder) => $builder->where('supplier_id', $supplierId));

        return view('admin.reports.purchases', [
            'purchases' => (clone $query)->latest('purchased_at')->latest('id')->paginate(20)->withQueryString(),
            'purchaseCount' => (clone $query)->count(),
            'subtotal' => (float) (clone $query)->sum('subtotal'),
            'discount' => (float) (clone $query)->sum('discount'),
            'total' => (float) (clone $query)->sum('total'),
            'paid' => (float) (clone $query)->sum('paid'),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'supplierId' => $supplierId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function sales(Request $request): View
    {
        [$from, $to] = $this->dates($request);
        $employeeId = $request->integer('employee_id') ?: null;
        $brandId = $request->integer('brand_id') ?: null;
        $query = Sale::query()->with('user')->withCount('items')
            ->whereBetween('sold_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($employeeId, fn (Builder $builder) => $builder->where('user_id', $employeeId))
            ->when($brandId, fn (Builder $builder) => $builder->whereHas('items.product', fn (Builder $product) => $product->where('brand_id', $brandId)));

        // Sales totals are invoice totals. Use the exact same filtered invoice IDs
        // for cost, so gross profit remains accurate when a brand is selected.
        $costQuery = SaleItem::query()->whereIn('sale_id', (clone $query)->select('id'));
        $cost = (float) $costQuery->selectRaw('COALESCE(SUM(unit_purchase_price * quantity), 0) AS cost')->value('cost');
        $total = (float) (clone $query)->sum('total');

        return view('admin.reports.sales', [
            'sales' => (clone $query)->latest('sold_at')->paginate(20)->withQueryString(),
            'saleCount' => (clone $query)->count(),
            'subtotal' => (float) (clone $query)->sum('subtotal'),
            'discount' => (float) (clone $query)->sum('discount'),
            'total' => $total,
            'profit' => $total - $cost,
            'employees' => User::query()->with('roles')->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'employeeId' => $employeeId,
            'brandId' => $brandId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function productSupplierSales(Request $request): View
    {
        [$from, $to] = $this->dates($request);
        $employeeId = $request->integer('employee_id') ?: null;

        $base = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('generic_names', 'generic_names.id', '=', 'products.generic_name_id')
            ->leftJoin('medicine_batches', 'medicine_batches.id', '=', 'sale_items.medicine_batch_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'medicine_batches.supplier_id')
            ->whereBetween('sales.sold_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($employeeId, fn (Builder $query) => $query->where('sales.user_id', $employeeId));

        $products = (clone $base)
            ->selectRaw("products.id AS product_id, products.name AS product_name, products.barcode, products.category, generic_names.name AS generic_name, COALESCE(suppliers.name, 'Unassigned supplier') AS supplier_name, SUM(sale_items.quantity) AS sale_units, SUM(CASE WHEN sale_items.stock_quantity > 0 THEN sale_items.stock_quantity ELSE sale_items.quantity * sale_items.units_per_sale_unit END) AS pieces_sold, SUM(sale_items.line_total) AS sales_amount, SUM(sale_items.unit_purchase_price * sale_items.quantity) AS cost_amount")
            ->groupBy('products.id', 'products.name', 'products.barcode', 'products.category', 'generic_names.name', 'suppliers.name')
            ->orderByDesc('sales_amount')
            ->get();

        $suppliers = (clone $base)
            ->selectRaw("COALESCE(suppliers.name, 'Unassigned supplier') AS supplier_name, COUNT(DISTINCT sale_items.product_id) AS product_count, SUM(sale_items.quantity) AS sale_units, SUM(CASE WHEN sale_items.stock_quantity > 0 THEN sale_items.stock_quantity ELSE sale_items.quantity * sale_items.units_per_sale_unit END) AS pieces_sold, SUM(sale_items.line_total) AS sales_amount, SUM(sale_items.unit_purchase_price * sale_items.quantity) AS cost_amount")
            ->groupBy('suppliers.name')
            ->orderByDesc('sales_amount')
            ->get();

        return view('admin.reports.product-supplier-sales', [
            'products' => $products,
            'suppliers' => $suppliers,
            'invoiceCount' => (clone $base)->distinct('sale_items.sale_id')->count('sale_items.sale_id'),
            'totalSales' => (float) $products->sum('sales_amount'),
            'totalCost' => (float) $products->sum('cost_amount'),
            'employees' => User::query()->with('roles')->orderBy('name')->get(),
            'employeeId' => $employeeId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function stock(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,in_stock,low,out'],
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';
        $query = $this->stockQuery()->when($search, fn (Builder $builder) => $builder->where(fn (Builder $inner) => $inner
            ->where('name', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%")));

        $stockSubquery = '(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id)';
        $query->when($status === 'out', fn (Builder $builder) => $builder->whereRaw("{$stockSubquery} = 0"))
            ->when($status === 'low', fn (Builder $builder) => $builder->whereRaw("{$stockSubquery} > 0 AND {$stockSubquery} <= products.reorder_level"))
            ->when($status === 'in_stock', fn (Builder $builder) => $builder->whereRaw("{$stockSubquery} > products.reorder_level"));

        return view('admin.reports.stock', [
            'products' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'stockPieces' => (int) MedicineBatch::query()->sum('quantity_available'),
            'purchaseValue' => (float) MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) AS value')->value('value'),
            'saleValue' => (float) MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * sale_price), 0) AS value')->value('value'),
            'batchCount' => MedicineBatch::query()->where('quantity_available', '>', 0)->count(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function needed(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $search = trim((string) ($filters['search'] ?? ''));
        $query = $this->criticalStockQuery()->with(['brand', 'genericName', 'batches.supplier'])
            ->when($search, fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%"));
        $products = $query->orderByRaw('stock_quantity = 0 DESC')->orderBy('stock_quantity')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.reports.needed', [
            'products' => $products,
            'neededCount' => $products->total(),
            'outOfStockCount' => $this->criticalStockQuery()->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id) = 0')->count(),
            'search' => $search,
        ]);
    }

    private function dates(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        ]);

        return [
            isset($data['from']) ? Carbon::parse($data['from']) : today()->startOfMonth(),
            isset($data['to']) ? Carbon::parse($data['to']) : today(),
        ];
    }

    private function stockQuery(): Builder
    {
        return Product::query()->with(['brand', 'genericName'])
            ->withCount(['batches as batch_count' => fn (Builder $query) => $query->where('quantity_available', '>', 0)])
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->selectSub(MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0)')->whereColumn('product_id', 'products.id'), 'purchase_value')
            ->selectSub(MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * sale_price), 0)')->whereColumn('product_id', 'products.id'), 'sale_value');
    }

    private function lowStockQuery(): Builder
    {
        return Product::query()->where('is_active', true)
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id) <= products.reorder_level');
    }

    private function criticalStockQuery(): Builder
    {
        return Product::query()->where('is_active', true)
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id) < 10');
    }
}
