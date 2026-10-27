<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadStatusHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'lead' => ['nullable', 'integer', 'exists:leads,id'], 'status' => ['nullable', 'integer', 'exists:crm_lead_statuses,id'],
            'user' => ['nullable', 'integer', 'exists:users,id'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $canViewAll = $request->user()->can('sales.view-all');
        $base = DB::table('lead_status_histories as history')
            ->join('leads as lead', 'history.lead_id', '=', 'lead.id')
            ->leftJoin('crm_lead_statuses as status', 'history.lead_status_id', '=', 'status.id')
            ->leftJoin('crm_lead_statuses as previous_status', 'history.previous_lead_status_id', '=', 'previous_status.id')
            ->leftJoin('users as changed_by', 'history.changed_by', '=', 'changed_by.id')
            ->select('history.*', 'lead.name as lead_name', 'lead.phone as lead_phone', 'status.name as status_name', 'status.badge_color', 'previous_status.name as previous_status_name', 'changed_by.name as changed_by_name')
            ->when(! $canViewAll, fn ($query) => $query->where('lead.owner_id', $request->user()->id));

        $history = (clone $base)->when($filters['lead'] ?? null, fn ($q, $id) => $q->where('history.lead_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $id) => $q->where('history.lead_status_id', $id))
            ->when($filters['user'] ?? null, fn ($q, $id) => $q->where('history.changed_by', $id))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('history.status_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('history.status_date', '<=', $date))
            ->orderByDesc('history.status_date')->orderByDesc('history.status_time')->orderByDesc('history.id')->paginate(25)->withQueryString();

        return view('leads.status-history-index', [
            'history' => $history, 'filters' => $filters, 'canViewAll' => $canViewAll,
            'leads' => (clone $base)->select('lead.id', 'lead.name', 'lead.phone')->distinct()->orderBy('lead.name')->get(),
            'statuses' => DB::table('crm_lead_statuses')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'users' => $canViewAll ? User::where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect(),
        ]);
    }
}
