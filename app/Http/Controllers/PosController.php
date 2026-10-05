<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PosSavedOrder;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        return $this->renderPos();
    }

    public function editSavedOrder(PosSavedOrder $savedOrder): View
    {
        $this->ensureSavedOrderAccess($savedOrder);
        abort_unless(in_array($savedOrder->status, ['draft', 'held'], true), 404);

        return $this->renderPos($savedOrder);
    }

    private function renderPos(?PosSavedOrder $savedOrder = null): View
    {
        $cartRows = old('items', $savedOrder?->items ?? []);
        $cartProductIds = collect($cartRows)->pluck('product_id')->filter()->map(fn($id) => (int) $id)->unique()->values();
        $catalog = $cartProductIds->isEmpty() ? collect() : $this->catalogQuery()->whereIn('products.id', $cartProductIds)->get()->map(fn(Product $product) => $this->catalogItem($product))->values();

        return view('pos.index', [
            'catalog' => $catalog,
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'customer_type', 'phone', 'due_balance']),
            'recentSales' => Sale::query()->where('user_id', request()->user()->id)->latest('sold_at')->limit(5)->get(),
            'savedOrder' => $savedOrder,
        ]);
    }

    /** Return one small, paginated POS catalog page. Never load the whole inventory into the browser. */
    public function catalog(Request $request): JsonResponse
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $search = trim((string) ($data['search'] ?? ''));
        $products = $this->catalogQuery()
            ->when($search !== '', fn(Builder $query) => $query->where(function (Builder $match) use ($search) {
                $match->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%")
                    ->orWhere('products.category', 'like', "%{$search}%")
                    ->orWhereHas('genericName', fn(Builder $generic) => $generic->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('batches.supplier', fn(Builder $supplier) => $supplier->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy('products.name')
            ->paginate(15);

        return response()->json([
            'data' => $products->getCollection()->map(fn(Product $product) => $this->catalogItem($product))->values(),
            'meta' => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'per_page' => $products->perPage(), 'total' => $products->total()],
        ]);
    }

    private function catalogQuery(): Builder
    {
        return Product::query()
            ->with(['genericName:id,name', 'batches.supplier:id,name', 'batches' => fn($query) => $query
                ->where('quantity_available', '>', 0)
                ->where(fn(Builder $expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today()))
                ->orderByRaw('expires_on IS NULL')->orderBy('expires_on')->orderBy('id')])
            ->where('is_active', true)
            ->whereHas('batches', fn(Builder $query) => $query
                ->where('quantity_available', '>', 0)
                ->where(fn(Builder $expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today())));
    }

    private function catalogItem(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'generic' => $product->genericName?->name,
            'category' => $product->category,
            'supplier' => $product->batches->first()?->supplier?->name,
            'pieces_per_strip' => $product->pieces_per_strip,
            'sell_by_piece' => $product->sell_by_piece,
            'sell_by_strip' => $product->sell_by_strip,
            'piece_stock' => $product->batches->sum('quantity_available'),
            'strip_stock' => $product->batches->sum(fn($batch) => intdiv($batch->quantity_available, max(1, $product->pieces_per_strip))),
            'piece_price' => $product->batches->first()?->sale_price,
            'strip_price' => $product->batches->first(fn($batch) => $batch->strip_sale_price !== null)?->strip_sale_price,
            'batches' => $product->batches->map(fn($batch) => ['quantity' => $batch->quantity_available, 'piece_price' => (float) $batch->sale_price, 'strip_price' => $batch->strip_sale_price !== null ? (float) $batch->strip_sale_price : null])->values(),
        ];
    }

    public function store(StoreSaleRequest $request, SaleService $saleService): RedirectResponse
    {
        $sale = $saleService->create($request->validated(), $request->user());

        if ($savedOrderId = $request->integer('saved_order_id')) {
            $savedOrder = PosSavedOrder::query()->find($savedOrderId);
            if ($savedOrder && in_array($savedOrder->status, ['draft', 'held'], true)) {
                $this->ensureSavedOrderAccess($savedOrder);
                $savedOrder->update(['status' => 'completed', 'sale_id' => $sale->id, 'completed_at' => now()]);
            }
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale completed successfully.');
    }

    public function saveOrder(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $extra = $request->validate(['status' => ['required', 'in:draft,held'], 'note' => ['nullable', 'string', 'max:1000']]);
        $prefix = $extra['status'] === 'held' ? 'HLD' : 'DRF';

        $order = PosSavedOrder::create([
            ...$data,
            'order_number' => "{$prefix}-" . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
            'user_id' => $request->user()->id,
            'status' => $extra['status'],
            'note' => blank($extra['note'] ?? null) ? null : $extra['note'],
        ]);

        return redirect()->route('pos.saved-orders.index', ['status' => $order->status])->with('success', ucfirst($order->status) . ' order ' . $order->order_number . ' saved. Stock has not been deducted.');
    }

    public function savedOrders(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', 'in:draft,held'], 'search' => ['nullable', 'string', 'max:100']]);
        $status = $filters['status'] ?? 'held';
        $search = trim((string) ($filters['search'] ?? ''));
        $query = PosSavedOrder::query()->with(['user:id,name', 'customer:id,name,phone'])->where('status', $status)
            ->when(! $request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']), fn($builder) => $builder->where('user_id', $request->user()->id))
            ->when($search, fn($builder) => $builder->where(fn($inner) => $inner->where('order_number', 'like', "%{$search}%")->orWhere('note', 'like', "%{$search}%")));

        return view('pos.saved-orders', ['orders' => $query->latest()->paginate(20)->withQueryString(), 'status' => $status, 'search' => $search]);
    }

    public function destroySavedOrder(PosSavedOrder $savedOrder): RedirectResponse
    {
        $this->ensureSavedOrderAccess($savedOrder);
        abort_unless(in_array($savedOrder->status, ['draft', 'held'], true), 404);
        $savedOrder->delete();

        return back()->with('success', 'Saved order removed.');
    }

    public function history(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $search = trim((string) ($filters['search'] ?? ''));
        $query = Sale::query()->with(['user:id,name', 'customer:id,name,phone'])->withCount('items')
            ->when(! $request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']), fn($builder) => $builder->where('user_id', $request->user()->id))
            ->when($search, fn($builder) => $builder->where(fn($inner) => $inner->where('invoice_number', 'like', "%{$search}%")->orWhereHas('customer', fn($customers) => $customers->where('name', 'like', "%{$search}%"))))
            ->when($filters['from'] ?? null, fn($builder, $from) => $builder->whereDate('sold_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($builder, $to) => $builder->whereDate('sold_at', '<=', $to));

        return view('pos.history', ['sales' => $query->latest('sold_at')->paginate(25)->withQueryString(), 'search' => $search, 'from' => $filters['from'] ?? '', 'to' => $filters['to'] ?? '']);
    }

    private function ensureSavedOrderAccess(PosSavedOrder $savedOrder): void
    {
        abort_unless(request()->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']) || $savedOrder->user_id === request()->user()->id, 403);
    }
}
