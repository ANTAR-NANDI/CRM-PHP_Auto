<?php

namespace App\Http\Controllers;

use App\Models\PromotionEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventOperationController extends Controller
{
    public function index(string $type): View
    {
        return view('event-operations.index', [
            'type' => $type,
            'title' => $type === 'activity' ? 'Event Activity' : 'Event Expense',
            'records' => DB::table($type === 'activity' ? 'event_activities' : 'event_expenses')
                ->join('promotion_events', ($type === 'activity' ? 'event_activities' : 'event_expenses').'.promotion_event_id', '=', 'promotion_events.id')
                ->select(($type === 'activity' ? 'event_activities' : 'event_expenses').'.*', 'promotion_events.title as event_title')
                ->latest('id')->paginate(20),
        ]);
    }

    public function create(string $type): View
    {
        return view('event-operations.create', ['type' => $type, 'title' => $type === 'activity' ? 'Event Activity' : 'Event Expense', 'events' => PromotionEvent::orderByDesc('start_date')->get(['id', 'title'])]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $data = $request->validate(['promotion_event_id' => ['required', 'exists:promotion_events,id'], 'title' => ['required', 'string', 'max:255'], 'activity_date' => ['nullable', 'date'], 'amount' => ['nullable', 'numeric', 'min:0'], 'remarks' => ['nullable', 'string', 'max:5000']]);
        DB::table($type === 'activity' ? 'event_activities' : 'event_expenses')->insert($data + ['created_at' => now(), 'updated_at' => now()]);
        return redirect()->route($type === 'activity' ? 'event-activities.index' : 'event-expenses.index')->with('success', 'Event '.($type === 'activity' ? 'activity' : 'expense').' saved successfully.');
    }
}
