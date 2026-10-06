<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubType;
use App\Models\ActivitySubjectType;
use App\Models\ActivityType;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Todo;
use App\Models\TodoType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
            ->latest('from_at')->get();

        return view('activities.index', [
            'activities' => $activities,
            'types' => ActivityType::where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $subjectTypes = ActivitySubjectType::where('is_active', true)->orderBy('name')->get();
        $requestedType = $request->query('subject_type');
        $defaultSubjectType = $subjectTypes->contains('key', $requestedType) ? $requestedType : 'customer';

        return view('activities.create', [
            'types' => ActivityType::where('is_active', true)->orderBy('name')->get(),
            'subjectTypes' => $subjectTypes,
            'defaultSubjectType' => $defaultSubjectType,
            'defaultSubjectId' => $request->query('subject_id'),
            'todoTypes' => TodoType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_type_id' => ['required', 'exists:activity_types,id'],
            'activity_sub_type_id' => ['nullable', 'exists:activity_sub_types,id'],
            'subject_type' => ['required', Rule::exists('activity_subject_types', 'key')->where('is_active', true)],
            'subject_id' => ['nullable', 'integer'],
            'activity_with' => ['nullable', 'string', 'max:255'],
            'from_at' => ['required', 'date'],
            'to_at' => ['nullable', 'date', 'after_or_equal:from_at'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'keep_todo' => ['nullable', 'boolean'],
            'todo_type_id' => ['required_if:keep_todo,1', 'nullable', 'exists:todo_types,id'],
            'todo_due_at' => ['required_if:keep_todo,1', 'nullable', 'date'],
            'todo_remind_before_minutes' => ['required_if:keep_todo,1', 'nullable', Rule::in([0, 5, 10, 15, 30, 60, 1440])],
            'todo_note' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'attachment' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! ActivitySubType::where('id', $data['activity_sub_type_id'] ?? null)->where('activity_type_id', $data['activity_type_id'])->exists() && ! empty($data['activity_sub_type_id'])) {
            return back()->withErrors(['activity_sub_type_id' => 'Choose a subtype from the selected activity type.'])->withInput();
        }
        $subjectClass = $this->subjectModel($data['subject_type']);
        if (! $subjectClass) {
            return back()->withErrors(['subject_type' => 'Choose a supported Activity For category.'])->withInput();
        }
        if (! empty($data['subject_id']) && ! $subjectClass::whereKey($data['subject_id'])->exists()) {
            return back()->withErrors(['subject_id' => 'Choose a valid '.$data['subject_type'].'.'])->withInput();
        }

        $data['user_id'] = $request->user()->id;
        $data['keep_todo'] = $request->boolean('keep_todo');
        if ($request->hasFile('attachment')) $data['attachment_path'] = $request->file('attachment')->store('activity-attachments', 'public');
        unset($data['attachment']);

        DB::transaction(function () use ($data, $request) {
            $activityData = collect($data)->except(['todo_type_id', 'todo_due_at', 'todo_remind_before_minutes', 'todo_note'])->all();
            Activity::create($activityData);

            if ($data['keep_todo']) {
                Todo::create([
                    'todo_type_id' => $data['todo_type_id'],
                    'assigned_to' => $request->user()->id,
                    'subject_type' => $data['subject_type'],
                    'subject_id' => $data['subject_id'] ?? null,
                    'task_with' => $data['activity_with'] ?? null,
                    'due_at' => $data['todo_due_at'],
                    'priority' => $data['priority'],
                    'remind_before_minutes' => $data['todo_remind_before_minutes'],
                    'note' => $data['todo_note'] ?? null,
                ]);
            }
        });

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
        $type = (string) $request->query('type');
        if (! ActivitySubjectType::where('key', $type)->where('is_active', true)->exists()) return response()->json([]);

        return response()->json(match ($type) {
            'lead' => Lead::query()->when($query, fn ($builder) => $builder->where(fn ($match) => $match->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%")))->orderBy('name')->limit(50)->get(['id', 'name', 'phone'])->map(fn (Lead $lead) => ['id' => $lead->id, 'label' => trim($lead->name.' · '.$lead->phone)]),
            'contact' => Contact::query()->when($query, fn ($builder) => $builder->where(fn ($match) => $match->where('name', 'like', "%{$query}%")->orWhere('mobile_1', 'like', "%{$query}%")))->orderBy('name')->limit(50)->get(['id', 'name', 'mobile_1'])->map(fn (Contact $contact) => ['id' => $contact->id, 'label' => trim($contact->name.' · '.$contact->mobile_1)]),
            'organization' => Organization::query()->where('is_active', true)->when($query, fn ($builder) => $builder->where('name', 'like', "%{$query}%"))->orderBy('name')->limit(50)->get(['id', 'name'])->map(fn (Organization $organization) => ['id' => $organization->id, 'label' => $organization->name]),
            default => Customer::query()->where('is_active', true)->when($query, fn ($builder) => $builder->where(fn ($match) => $match->where('name', 'like', "%{$query}%")->orWhere('phone', 'like', "%{$query}%")))->orderBy('name')->limit(50)->get(['id', 'name', 'phone'])->map(fn (Customer $customer) => ['id' => $customer->id, 'label' => trim($customer->name.' · '.$customer->phone)]),
        });
    }

    private function subjectModel(string $type): ?string
    {
        return match ($type) {
            'customer' => Customer::class,
            'lead' => Lead::class,
            'contact' => Contact::class,
            'organization' => Organization::class,
            default => null,
        };
    }
}
