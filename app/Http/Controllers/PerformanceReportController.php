<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerformanceReportController extends Controller
{
    private const SALE_POINT = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $filters['from'] = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $filters['to'] = $filters['to'] ?? now()->toDateString();
        $canViewAll = $request->user()->can('sales.view-all');
        if (! $canViewAll) $filters['user'] = $request->user()->id;

        $users = User::query()
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->whereKey($userId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $activityHeaders = DB::table('activity_types')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'point']);
        $leadHeaders = DB::table('crm_lead_statuses')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'point']);
        $salesHeaders = DB::table('item_categories')->orderBy('name')->pluck('name')->values();

        $activityCounts = DB::table('activities')
            ->selectRaw('user_id, activity_type_id, COUNT(*) as total')
            ->whereBetween('from_at', [$filters['from'].' 00:00:00', $filters['to'].' 23:59:59'])
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->groupBy('user_id', 'activity_type_id')
            ->get()->groupBy('user_id')
            ->map(fn ($records) => $records->pluck('total', 'activity_type_id'));

        $leadCounts = DB::table('lead_status_histories')
            ->selectRaw('changed_by, lead_status_id, COUNT(DISTINCT lead_id) as total')
            ->whereBetween('status_date', [$filters['from'], $filters['to']])
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where('changed_by', $userId))
            ->groupBy('changed_by', 'lead_status_id')
            ->get()->groupBy('changed_by')
            ->map(fn ($records) => $records->pluck('total', 'lead_status_id'));

        $salesCounts = DB::table('customer_vehicle_sales as sale')
            ->join('products as product', 'sale.product_id', '=', 'product.id')
            ->leftJoin('item_categories as category', 'product.item_category_id', '=', 'category.id')
            ->selectRaw("sale.sales_person_id, COALESCE(category.name, product.category, 'Uncategorized') as category_name, SUM(sale.quantity) as total")
            ->whereBetween('sale.sale_date', [$filters['from'], $filters['to']])
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where('sale.sales_person_id', $userId))
            ->groupBy('sale.sales_person_id', 'category_name')
            ->get()->groupBy('sales_person_id')
            ->map(fn ($records) => $records->pluck('total', 'category_name'));

        $rows = $users->map(function (User $user) use ($activityCounts, $leadCounts, $salesCounts, $activityHeaders, $leadHeaders) {
            $activities = $activityCounts->get($user->id, collect());
            $leads = $leadCounts->get($user->id, collect());
            $sales = $salesCounts->get($user->id, collect());
            $activityScore = $activityHeaders->sum(fn ($header) => $activities->get($header->id, 0) * $header->point);
            $leadScore = $leadHeaders->sum(fn ($header) => $leads->get($header->id, 0) * $header->point);
            $salesScore = $sales->sum() * self::SALE_POINT;

            return ['name' => $user->name, 'activities' => $activities, 'leads' => $leads, 'sales' => $sales, 'total_score' => $activityScore + $leadScore + $salesScore];
        });

        return view('performance.index', compact('filters', 'users', 'activityHeaders', 'leadHeaders', 'salesHeaders', 'rows', 'canViewAll') + ['salePoint' => self::SALE_POINT]);
    }
}
