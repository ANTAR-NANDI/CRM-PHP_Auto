<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Activity;
use App\Models\CustomerVehicleSale;
use App\Models\GoodsReceipt;
use App\Models\Lead;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
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
        $calendarStart = $today->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $today->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $calendarTodos = Todo::query()
            ->whereBetween('due_at', [$calendarStart, $calendarEnd->copy()->endOfDay()])
            ->where('status', '!=', 'completed')
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn (Todo $todo) => $todo->due_at->toDateString());
        $leadStatusSummary = DB::table('crm_lead_statuses')
            ->leftJoin('leads', 'leads.lead_status_id', '=', 'crm_lead_statuses.id')
            ->select('crm_lead_statuses.id', 'crm_lead_statuses.name', 'crm_lead_statuses.badge_color', DB::raw('COUNT(leads.id) AS total'))
            ->where('crm_lead_statuses.is_active', true)
            ->groupBy('crm_lead_statuses.id', 'crm_lead_statuses.name', 'crm_lead_statuses.badge_color')
            ->orderBy('crm_lead_statuses.name')
            ->get();
        $pipelineSummary = DB::table('crm_pipelines')
            ->leftJoin('leads', 'leads.pipeline_id', '=', 'crm_pipelines.id')
            ->select('crm_pipelines.id', 'crm_pipelines.name', DB::raw('COUNT(leads.id) AS total'))
            ->where('crm_pipelines.is_active', true)
            ->groupBy('crm_pipelines.id', 'crm_pipelines.name')
            ->orderBy('crm_pipelines.name')
            ->get();

        $canViewAllSales = $request->user()->can('sales.view-all');
        $vehicleSales = CustomerVehicleSale::query()
            ->when(! $canViewAllSales, fn ($query) => $query->where('sales_person_id', $request->user()->id));
        $salesCollections = DB::table('sales_collections')
            ->join('customer_vehicle_sales as collection_sale', 'sales_collections.customer_vehicle_sale_id', '=', 'collection_sale.id')
            ->when(! $canViewAllSales, fn ($query) => $query->where('collection_sale.sales_person_id', $request->user()->id));

        $totalVehicleSales = (float) (clone $vehicleSales)->sum('final_price');
        $totalBookingReceived = (float) (clone $vehicleSales)->sum('paid_amount');
        $totalLaterCollections = (float) (clone $salesCollections)->sum('sales_collections.amount');
        $totalReceived = $totalBookingReceived + $totalLaterCollections;
        $customerDue = max(0, $totalVehicleSales - $totalReceived);
        $todayVehicleSales = (float) (clone $vehicleSales)->whereDate('sale_date', $today)->sum('final_price');
        $todayBookingReceived = (float) (clone $vehicleSales)->whereDate('payment_date', $today)->sum('paid_amount');
        $todayLaterCollections = (float) (clone $salesCollections)->whereDate('sales_collections.received_at', $today)->sum('sales_collections.amount');

        $receivedByProduct = DB::table('goods_receipt_items')
            ->selectRaw('product_id, SUM(quantity) AS received_quantity, SUM(amount) AS received_value')
            ->groupBy('product_id');
        $soldByProduct = CustomerVehicleSale::query()
            ->selectRaw('product_id, SUM(quantity) AS sold_quantity')
            ->whereNotNull('product_id')
            ->groupBy('product_id');
        $vehicleInventory = Product::query()
            ->leftJoinSub($receivedByProduct, 'received', fn ($join) => $join->on('products.id', '=', 'received.product_id'))
            ->leftJoinSub($soldByProduct, 'sold', fn ($join) => $join->on('products.id', '=', 'sold.product_id'))
            ->where('products.is_active', true)
            ->select('products.id', 'products.name', 'products.part_no', 'products.unit')
            ->selectRaw('COALESCE(received.received_quantity, 0) AS received_quantity')
            ->selectRaw('COALESCE(sold.sold_quantity, 0) AS sold_quantity')
            ->selectRaw('GREATEST(COALESCE(received.received_quantity, 0) - COALESCE(sold.sold_quantity, 0), 0) AS available_quantity')
            ->orderBy('products.name')
            ->get();
        $vehiclesReceived = (float) $vehicleInventory->sum('received_quantity');
        $vehiclesAvailable = (float) $vehicleInventory->sum('available_quantity');
        $soldVehicles = (float) $vehicleInventory->sum('sold_quantity');
        $outOfStockCount = $vehicleInventory->filter(fn ($item) => (float) $item->available_quantity <= 0)->count();
        $vehiclePurchaseValue = (float) GoodsReceipt::query()->sum('total_amount');
        $chequeSummary = DB::table('cheques')->selectRaw("SUM(status = 'in_hand') AS in_hand, SUM(status = 'deposited') AS deposited, SUM(status IN ('cleared', 'honored')) AS cleared, SUM(status IN ('bounced', 'dishonored')) AS bounced")->first();
        $weeklyReceipts = collect(range(6, 0))->map(function (int $daysAgo) use ($vehicleSales, $salesCollections) {
            $date = today()->subDays($daysAgo);
            $booking = (float) (clone $vehicleSales)->whereDate('payment_date', $date)->sum('paid_amount');
            $collection = (float) (clone $salesCollections)->whereDate('sales_collections.received_at', $date)->sum('sales_collections.amount');

            return ['label' => $daysAgo === 0 ? 'Today' : $date->format('D'), 'date' => $date->format('d M'), 'total' => $booking + $collection];
        });

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
            'recentActivities' => Activity::query()->with(['type', 'user'])->whereBetween('from_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])->latest('from_at')->limit(8)->get(),
            'todoReminders' => Todo::query()->with(['type', 'assignee'])->where('status', '!=', 'completed')->where('due_at', '<=', $today->copy()->addDays(14)->endOfDay())->orderBy('due_at')->limit(6)->get(),
            'leadStatusSummary' => $leadStatusSummary,
            'pipelineSummary' => $pipelineSummary,
            'hotLeads' => Lead::query()->with(['owner', 'product'])->join('crm_lead_statuses', 'leads.lead_status_id', '=', 'crm_lead_statuses.id')->whereRaw('LOWER(crm_lead_statuses.name) = ?', ['hot'])->select('leads.*')->latest('leads.created_at')->limit(5)->get(),
            'warmLeads' => Lead::query()->with(['owner', 'product'])->join('crm_lead_statuses', 'leads.lead_status_id', '=', 'crm_lead_statuses.id')->whereRaw('LOWER(crm_lead_statuses.name) = ?', ['warm'])->select('leads.*')->latest('leads.created_at')->limit(5)->get(),
            'dashboardInventory' => Product::query()->where('is_active', true)->withSum('batches as stock_quantity', 'quantity_available')->orderBy('name')->limit(8)->get(),
            'calendarDays' => collect(CarbonPeriod::create($calendarStart, $calendarEnd)),
            'calendarTodos' => $calendarTodos,
            'calendarMonth' => $today->format('F Y'),
            'totalVehicleSales' => $totalVehicleSales,
            'totalBookingReceived' => $totalBookingReceived,
            'totalLaterCollections' => $totalLaterCollections,
            'totalReceived' => $totalReceived,
            'customerDue' => $customerDue,
            'todayVehicleSales' => $todayVehicleSales,
            'todayBookingReceived' => $todayBookingReceived,
            'todayLaterCollections' => $todayLaterCollections,
            'vehiclesReceived' => $vehiclesReceived,
            'vehiclesAvailable' => $vehiclesAvailable,
            'soldVehicles' => $soldVehicles,
            'outOfStockCount' => $outOfStockCount,
            'vehiclePurchaseValue' => $vehiclePurchaseValue,
            'chequeSummary' => $chequeSummary,
            'weeklyReceipts' => $weeklyReceipts,
            'maxWeeklyReceipt' => max(1, (float) $weeklyReceipts->max('total')),
            'vehicleInventory' => $vehicleInventory,
        ]);
    }
}
