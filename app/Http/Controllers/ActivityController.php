<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubType;
use App\Models\ActivityType;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', 'integer', 'exists:activity_types,id'],
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $activities = Activity::query()->with(['type', 'subType', 'user'])
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('activity_type_id', $type))
            ->when($filters['user'] ?? null, fn ($query, $user) => $query->where('user_id', $user))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('from_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('from_at', '<=', $date))
            ->latest('from_at')->paginate(20)->withQueryString();

        return view('activities.index', [
            'activities' => $activities,
            'types' => ActivityType::where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('activities.create', [
            'types' => ActivityType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_type_id' => ['required', 'exists:activity_types,id'],
            'activity_sub_type_id' => ['nullable', 'exists:activity_sub_types,id'],
            'subject_type' => ['required', Rule::in(['customer', 'lead'])],
            'subject_id' => ['nullable', 'integer'],
            'activity_with' => ['nullable', 'string', 'max:255'],
            'from_at' => ['required', 'date'],
            'to_at' => ['nullable', 'date', 'after_or_equal:from_at'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'keep_todo' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! ActivitySubType::where('id', $data['activity_sub_type_id'] ?? null)->where('activity_type_id', $data['activity_type_id'])->exists() && ! empty($data['activity_sub_type_id'])) {
            return back()->withErrors(['activity_sub_type_id' => 'Choose a subtype from the selected activity type.'])->withInput();
        }
        $subjectClass = $data['subject_type'] === 'lead' ? Lead::class : Customer::class;
        if (! empty($data['subject_id']) && ! $subjectClass::whereKey($data['subject_id'])->exists()) {
            return back()->withErrors(['subject_id' => 'Choose a valid '.$data['subject_type'].'.'])->withInput();
        }

        $data['user_id'] = $request->user()->id;
        $data['keep_todo'] = $request->boolean('keep_todo');
        if ($request->hasFile('attachment')) $data['attachment_path'] = $request->file('attachment')->store('activity-attachments', 'public');
        unset($data['attachment']);
        Activity::create($data);

        return redirect()->route('activities.index')->with('success', 'Activity saved successfully.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        if ($activity->attachment_path) Storage::disk('public')->delete($activity->attachment_path);
        $activity->delete();
        return back()->with('success', 'Activity deleted successfully.');
    }

    public function subTypes(ActivityType $activityType): JsonResponse
    {
        return response()->json($activityType->subTypes()->where('is_active', true)->orderBy('name')->get(['id', 'name']));
    }

    public function subjects(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));
        if ($request->query('type') === 'lead') {
            $leads = Lead::query()->when($query, fn ($builder) => $builder->where(fn ($match) => $match->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%")))->orderBy('name')->limit(50)->get(['id', 'name', 'phone']);
            return response()->json($leads->map(fn (Lead $lead) => ['id' => $lead->id, 'label' => trim($lead->name.' · '.$lead->phone)]));
        }
        $customers = Customer::query()->where('is_active', true)
            ->when($query, fn ($builder) => $builder->where(fn ($match) => $match->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%")))
            ->orderBy('name')->limit(50)->get(['id', 'name', 'phone']);

        return response()->json($customers->map(fn (Customer $customer) => ['id' => $customer->id, 'label' => trim($customer->name.' · '.$customer->phone)]));
    }
}
