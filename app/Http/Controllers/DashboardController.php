<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = today();
        $dates = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $salesFrom = isset($dates['from']) ? now()->parse($dates['from'])->startOfDay() : $today->copy()->startOfDay();
        $salesTo = isset($dates['to']) ? now()->parse($dates['to'])->endOfDay() : $today->copy()->endOfDay();
        $todaySales = (float) Sale::query()->whereDate('sold_at', $today)->sum('total');
        $todayProfit = (float) SaleItem::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('sold_at', $today))
            ->selectRaw('COALESCE(SUM(line_total - (unit_purchase_price * quantity)), 0) AS profit')
            ->value('profit');

        $lowStockBase = Product::query()->where('is_active', true)
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM medicine_batches WHERE medicine_batches.product_id = products.id) <= products.reorder_level');

        $lowStockProducts = (clone $lowStockBase)->orderBy('name')->limit(5)->get();
        $expiringBatches = MedicineBatch::query()->with('product')
            ->where('quantity_available', '>', 0)
            ->whereBetween('expires_on', [$today, $today->copy()->addDays(90)])
            ->orderBy('expires_on')->limit(5)->get();

        $weeklySales = collect(range(6, 1))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('d M'),
                'total' => (float) Sale::query()->whereDate('sold_at', $date)->sum('total'),
            ];
        })->push([
            'label' => 'Today',
            'date' => today()->format('d M'),
            'total' => $todaySales,
        ]);

        return view('dashboard', [
            'todaySales' => $todaySales,
            'todayProfit' => $todayProfit,
            'todayPurchases' => (float) Purchase::query()->whereDate('purchased_at', $today)->sum('total'),
            'lowStockCount' => (clone $lowStockBase)->count(),
            'expiringCount' => MedicineBatch::query()->where('quantity_available', '>', 0)->whereBetween('expires_on', [$today, $today->copy()->addDays(90)])->count(),
            'stockPieces' => (int) MedicineBatch::query()->sum('quantity_available'),
            'stockValue' => (float) MedicineBatch::query()->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) AS value')->value('value'),
            'activeEmployees' => User::query()->where('is_active', true)->count(),
            'presentToday' => Attendance::query()->whereDate('attendance_date', $today)->whereIn('status', ['present', 'half_day'])->count(),
            'weeklySales' => $weeklySales,
            'maxWeeklySale' => max(1, (float) $weeklySales->max('total')),
            'lowStockProducts' => $lowStockProducts,
            'expiringBatches' => $expiringBatches,
            'recentPurchases' => Purchase::query()->with('supplier')->latest('purchased_at')->latest('id')->limit(5)->get(),
            'salesFrom' => $salesFrom,
            'salesTo' => $salesTo,
            'employeeSales' => auth()->user()->hasRole('admin')
                ? Sale::query()
                    ->selectRaw('user_id, COUNT(*) AS invoice_count, COALESCE(SUM(total), 0) AS total_sales, COALESCE(SUM(paid), 0) AS total_paid, COALESCE(SUM(GREATEST(paid - total, 0)), 0) AS total_change_returned, COALESCE(SUM(due), 0) AS total_due')
                    ->with('user:id,name,employee_code')
                    ->whereBetween('sold_at', [$salesFrom, $salesTo])
                    ->groupBy('user_id')
                    ->orderByDesc('total_sales')
                    ->get()
                : collect(),
        ]);
    }
}
