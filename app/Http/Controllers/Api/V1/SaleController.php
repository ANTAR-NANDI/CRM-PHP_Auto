<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\PosSavedOrder;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyPharmacyRole(['admin', 'manager', 'cashier']);

        $sales = Sale::query()->with(['user:id,name,employee_code'])
            ->withCount('items')
            ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user->id))
            ->latest('sold_at')
            ->paginate(20);

        $bySalesperson = $isAdmin ? Sale::query()->selectRaw('user_id, COUNT(*) AS sales_count, COALESCE(SUM(total), 0) AS total_sales')
            ->with('user:id,name,employee_code')->groupBy('user_id')->orderByDesc('total_sales')->get() : collect();

        return response()->json([
            'data' => $sales->getCollection()->map(fn (Sale $sale) => $this->summary($sale)),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
                'salespeople' => $bySalesperson->map(fn (Sale $sale) => [
                    'employee' => ['id' => $sale->user?->id, 'name' => $sale->user?->name, 'employee_code' => $sale->user?->employee_code],
                    'sales_count' => (int) $sale->sales_count,
                    'total_sales' => (float) $sale->total_sales,
                ])->values(),
            ],
        ]);
    }

    public function store(StoreSaleRequest $request, SaleService $saleService): JsonResponse
    {
        $sale = $saleService->create($request->validated(), $request->user());

        if ($savedOrderId = $request->integer('saved_order_id')) {
            $order = PosSavedOrder::query()->where('status', 'held')->find($savedOrderId);
            if ($order && ($order->user_id === $request->user()->id || $request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']))) {
                $order->update(['status' => 'completed', 'sale_id' => $sale->id, 'completed_at' => now()]);
            }
        }

        return response()->json([
            'message' => 'Sale completed successfully.',
            'data' => $this->detail($sale),
        ], 201);
    }

    public function show(Request $request, Sale $sale): JsonResponse
    {
        abort_unless($sale->user_id === $request->user()->id || $request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']), 403);

        return response()->json(['data' => $this->detail($sale->load(['user:id,name,employee_code', 'customer:id,name,customer_type,phone', 'items.product:id,name']))]);
    }

    private function summary(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'customer_type' => $sale->customer_type,
            'total' => (float) $sale->total,
            'paid' => (float) $sale->paid,
            'due' => (float) $sale->due,
            'items_count' => $sale->items_count ?? $sale->items->count(),
            'sold_at' => $sale->sold_at?->toISOString(),
            'employee' => $sale->user ? ['id' => $sale->user->id, 'name' => $sale->user->name, 'employee_code' => $sale->user->employee_code] : null,
        ];
    }

    private function detail(Sale $sale): array
    {
        return [
            ...$this->summary($sale),
            'subtotal' => (float) $sale->subtotal,
            'discount' => (float) $sale->discount,
            'change' => (float) $sale->change,
            'payment_method' => $sale->payment_method,
            'employee' => $sale->user ? ['id' => $sale->user->id, 'name' => $sale->user->name, 'employee_code' => $sale->user->employee_code] : null,
            'customer' => $sale->customer ? ['id' => $sale->customer->id, 'name' => $sale->customer->name, 'type' => $sale->customer->customer_type, 'phone' => $sale->customer->phone] : null,
            'items' => $sale->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'sale_unit' => $item->sale_unit,
                'quantity' => $item->quantity,
                'units_per_sale_unit' => $item->units_per_sale_unit,
                'unit_price' => (float) $item->unit_sale_price,
                'line_total' => (float) $item->line_total,
            ])->values(),
        ];
    }
}
