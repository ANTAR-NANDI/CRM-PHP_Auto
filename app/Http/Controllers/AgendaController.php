<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\PreEventPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function create(): View
    {
        return view('agendas.create', ['segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'plans' => PreEventPlan::orderByDesc('tentative_date')->get(['id', 'title']), 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Agenda::create($this->validatedAgenda($request));
        return redirect()->route('agendas.index')->with('success', 'Agenda saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'plan' => ['nullable', 'integer'], 'status' => ['nullable', 'in:pending,approved,in_progress,completed,supervision,cancelled'], 'search' => ['nullable', 'string', 'max:255']]);
        $agendas = Agenda::query()->leftJoin('crm_segments as segment', 'agendas.segment_id', '=', 'segment.id')->select('agendas.*', 'segment.name as segment_name')->with(['plan', 'responsible', 'latestUpdate'])
            ->when($filters['segment'] ?? null, fn ($query, $id) => $query->where('agendas.segment_id', $id))
            ->when($filters['plan'] ?? null, fn ($query, $id) => $query->where('agendas.pre_event_plan_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('agendas.status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('agendas.title', 'like', "%{$search}%"))
            ->latest('agendas.start_date')->paginate(20)->withQueryString();
        return view('agendas.index', ['agendas' => $agendas, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'plans' => PreEventPlan::orderBy('title')->get(['id', 'title'])]);
    }

    public function show(Agenda $agenda): View
    {
        $agenda->load(['plan', 'responsible', 'updates.user']);
        return view('agendas.show', compact('agenda'));
    }

    public function storeUpdate(Request $request, Agenda $agenda): RedirectResponse
    {
        $data = $request->validate([
            'update_note' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:approved,supervision,cancelled'],
        ]);

        $agenda->updates()->create([...$data, 'user_id' => $request->user()->id]);
        $agenda->update(['status' => $data['status']]);

        return redirect()->route('agendas.index')->with('success', 'Agenda update saved successfully.');
    }

    private function validatedAgenda(Request $request): array
    {
        return $request->validate([
            'segment_id' => ['nullable', 'exists:crm_segments,id'],
            'title' => ['required', 'string', 'max:255'],
            'pre_event_plan_id' => ['nullable', 'exists:pre_event_plans,id'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:pending,approved,in_progress,completed,supervision,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
