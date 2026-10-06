<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EventType;
use App\Models\PromotionEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromotionEventController extends Controller
{
    public function create(): View
    {
        return view('promotion-events.create', ['segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'types' => EventType::where('is_active', true)->orderBy('name')->get(), 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'contacts' => Contact::orderBy('name')->limit(200)->get(['id', 'name', 'mobile_1'])]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', 'in:upcoming,ongoing,completed,cancelled'], 'type' => ['nullable', 'integer'], 'segment' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'search' => ['nullable', 'string', 'max:255']]);
        $filters['status'] = $filters['status'] ?? 'upcoming';
        $events = PromotionEvent::query()->leftJoin('crm_segments as segment', 'promotion_events.segment_id', '=', 'segment.id')->leftJoin('event_types as type', 'promotion_events.event_type_id', '=', 'type.id')->leftJoin('users as supervisor', 'promotion_events.supervisor_id', '=', 'supervisor.id')->select('promotion_events.*', 'segment.name as segment_name', 'type.name as type_name', 'supervisor.name as supervisor_name')->where('promotion_events.status', $filters['status'])->when($filters['type'] ?? null, fn ($query, $id) => $query->where('event_type_id', $id))->when($filters['segment'] ?? null, fn ($query, $id) => $query->where('segment_id', $id))->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('start_date', '>=', $date))->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('start_date', '<=', $date))->when($filters['search'] ?? null, fn ($query, $term) => $query->where(fn ($match) => $match->where('promotion_events.title', 'like', "%{$term}%")->orWhere('supervisor.name', 'like', "%{$term}%")->orWhere('type.name', 'like', "%{$term}%")))->latest('start_date')->paginate(20)->withQueryString();
        return view('promotion-events.index', ['events' => $events, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'types' => EventType::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['segment_id' => ['nullable', 'exists:crm_segments,id'], 'event_type_id' => ['required', 'exists:event_types,id'], 'title' => ['required', 'string', 'max:255'], 'supervisor_id' => ['nullable', 'exists:users,id'], 'attendee_ids' => ['nullable', 'array'], 'attendee_ids.*' => ['integer', 'exists:contacts,id'], 'attachment' => ['nullable', 'file', 'max:5120'], 'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'], 'remarks' => ['nullable', 'string', 'max:5000']]);
        if ($request->hasFile('attachment')) $data['attachment_path'] = $request->file('attachment')->store('event-attachments', 'public');
        unset($data['attachment']); PromotionEvent::create($data);
        return redirect()->route('events.index')->with('success', 'Event/campaign saved successfully.');
    }
}
