<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function overview(): JsonResponse
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();
        $monthlySales = (float) Sale::query()->whereDate('sold_at', '>=', $monthStart)->sum('total');
        $monthlyCost = (float) SaleItem::query()->whereHas('sale', fn ($query) => $query->whereDate('sold_at', '>=', $monthStart))
            ->selectRaw('COALESCE(SUM(unit_purchase_price * quantity), 0) AS cost')->value('cost');
        $lowStock = Product::query()->where('is_active', true)
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id) <= products.reorder_level');

        return response()->json(['data' => [
            'reports' => [
                'today_sales' => (float) Sale::query()->whereDate('sold_at', $today)->sum('total'),
                'monthly_sales' => $monthlySales,
                'monthly_profit' => $monthlySales - $monthlyCost,
                'monthly_purchases' => (float) Purchase::query()->whereDate('purchased_at', '>=', $monthStart)->sum('total'),
                'stock_value' => (float) MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) AS value')->value('value'),
                'low_stock_count' => (clone $lowStock)->count(),
            ],
            'modules' => [
                'products' => Product::query()->count(),
                'suppliers' => Supplier::query()->where('is_active', true)->count(),
                'customers' => Customer::query()->where('is_active', true)->count(),
                'purchases' => Purchase::query()->count(),
                'employees' => User::query()->where('is_active', true)->count(),
            ],
            'recent_purchases' => Purchase::query()->with('supplier:id,name')->latest('purchased_at')->limit(5)->get()
                ->map(fn (Purchase $purchase) => ['invoice_number' => $purchase->invoice_number, 'supplier' => $purchase->supplier?->name, 'total' => (float) $purchase->total, 'purchased_at' => $purchase->purchased_at?->toDateString()]),
            'low_stock' => (clone $lowStock)->orderBy('stock_quantity')->limit(5)->get(['id', 'name', 'reorder_level'])
                ->map(fn (Product $product) => ['id' => $product->id, 'name' => $product->name, 'stock' => (int) ($product->stock_quantity ?? 0), 'reorder_level' => $product->reorder_level]),
        ]]);
    }

    public function employeeSales(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = isset($data['from']) ? now()->parse($data['from'])->startOfDay() : today()->startOfMonth();
        $to = isset($data['to']) ? now()->parse($data['to'])->endOfDay() : now()->endOfDay();
        $employees = Sale::query()->selectRaw('user_id, COUNT(*) AS invoice_count, COALESCE(SUM(total), 0) AS total_sales, COALESCE(SUM(paid), 0) AS total_paid, COALESCE(SUM(due), 0) AS total_due')
            ->with('user:id,name,employee_code')->whereBetween('sold_at', [$from, $to])->groupBy('user_id')->orderByDesc('total_sales')->get();

        return response()->json(['data' => [
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'employees' => $employees->map(fn (Sale $sale) => [
                'employee' => ['id' => $sale->user?->id, 'name' => $sale->user?->name, 'employee_code' => $sale->user?->employee_code],
                'invoice_count' => (int) $sale->invoice_count, 'total_sales' => (float) $sale->total_sales,
                'total_paid' => (float) $sale->total_paid, 'total_due' => (float) $sale->total_due,
            ])->values(),
        ]]);
    }
}
