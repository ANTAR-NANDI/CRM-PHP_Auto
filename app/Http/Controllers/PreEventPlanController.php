<?php

namespace App\Http\Controllers;

use App\Models\PreEventPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreEventPlanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', 'in:pending,approved,in_progress,completed,cancelled'], 'supervisor' => ['nullable', 'integer', 'exists:users,id'], 'search' => ['nullable', 'string', 'max:255']]);
        $plans = PreEventPlan::with('supervisor')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['supervisor'] ?? null, fn ($query, $supervisor) => $query->where('supervisor_id', $supervisor))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($match) => $match->where('title', 'like', "%{$search}%")->orWhereHas('supervisor', fn ($user) => $user->where('name', 'like', "%{$search}%"))))
            ->latest('tentative_date')->paginate(20)->withQueryString();
        return view('pre-event-plans.index', ['plans' => $plans, 'filters' => $filters, 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function create(): View { return view('pre-event-plans.create', ['users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])]); }

    public function store(Request $request): RedirectResponse
    {
        PreEventPlan::create($request->validate(['title' => ['required', 'string', 'max:255'], 'tentative_date' => ['nullable', 'date'], 'description' => ['nullable', 'string', 'max:5000'], 'remarks' => ['nullable', 'string', 'max:5000'], 'supervisor_id' => ['nullable', 'exists:users,id'], 'status' => ['required', 'in:pending,approved,in_progress,completed,cancelled']]));
        return redirect()->route('pre-event-plans.index')->with('success', 'Pre-event plan saved successfully.');
    }
}
