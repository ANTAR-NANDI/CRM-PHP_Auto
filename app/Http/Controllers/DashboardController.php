<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Activity;
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
        ]);
    }
}
