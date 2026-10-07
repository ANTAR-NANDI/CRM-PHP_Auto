<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubType;
use App\Models\ActivitySubjectType;
use App\Models\ActivityType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivitySetupController extends Controller
{
    public function types(): View { return view('activity-setup.types', ['types' => ActivityType::withCount('subTypes')->orderBy('name')->paginate(20)]); }
    public function subTypes(): View { return view('activity-setup.sub-types', ['subTypes' => ActivitySubType::with('type')->orderBy('name')->paginate(20), 'types' => ActivityType::where('is_active', true)->orderBy('name')->get()]); }
    public function subjectTypes(): View { return view('activity-setup.subject-types', ['subjectTypes' => ActivitySubjectType::orderBy('name')->paginate(20)]); }
    public function index(): View
    {
        return view('activity-setup.index', [
            'types' => ActivityType::with('subTypes')->orderBy('name')->get(),
            'subjectTypes' => ActivitySubjectType::orderBy('name')->get(),
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        ActivityType::create($request->validate(['name' => ['required', 'string', 'max:100', 'unique:activity_types,name']]) + ['is_active' => true]);
        return back()->with('success', 'Activity type added.');
    }

    public function updateType(Request $request, ActivityType $activityType): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('activity_types', 'name')->ignore($activityType)], 'is_active' => ['nullable', 'boolean']]);
        $activityType->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Activity type updated.');
    }

    public function destroyType(ActivityType $activityType): RedirectResponse
    {
        if ($activityType->activities()->exists()) return back()->with('error', 'This type is already used by activities. Mark it inactive instead.');
        $activityType->delete();
        return back()->with('success', 'Activity type deleted.');
    }

    public function storeSubType(Request $request): RedirectResponse
    {
        $data = $request->validate(['activity_type_id' => ['required', 'exists:activity_types,id'], 'name' => ['required', 'string', 'max:100']]);
        $exists = ActivitySubType::where('activity_type_id', $data['activity_type_id'])->where('name', $data['name'])->exists();
        if ($exists) return back()->withErrors(['sub_type_name' => 'This sub type already exists for the selected activity type.']);
        ActivitySubType::create($data + ['is_active' => true]);
        return back()->with('success', 'Activity sub type added.');
    }

    public function updateSubType(Request $request, ActivitySubType $activitySubType): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'is_active' => ['nullable', 'boolean']]);
        $activitySubType->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Activity sub type updated.');
    }

    public function destroySubType(ActivitySubType $activitySubType): RedirectResponse
    {
        $activitySubType->delete();
        return back()->with('success', 'Activity sub type deleted.');
    }

    public function storeSubjectType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:activity_subject_types,name'],
            'key' => ['required', Rule::in(['customer', 'lead', 'contact', 'organization']), 'unique:activity_subject_types,key'],
        ]);

        ActivitySubjectType::create($data + ['is_active' => true]);
        return back()->with('success', 'Activity For category added.');
    }

    public function updateSubjectType(Request $request, ActivitySubjectType $activitySubjectType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('activity_subject_types', 'name')->ignore($activitySubjectType)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $activitySubjectType->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Activity For category updated.');
    }

    public function destroySubjectType(ActivitySubjectType $activitySubjectType): RedirectResponse
    {
        if ($activitySubjectType->key === 'customer') {
            return back()->with('error', 'Customer is a protected CRM category and cannot be deleted.');
        }

        if (\App\Models\Activity::where('subject_type', $activitySubjectType->key)->exists()) {
            return back()->with('error', 'This category is already used by activities. Mark it inactive instead.');
        }

        $activitySubjectType->delete();
        return back()->with('success', 'Activity For category deleted.');
    }
}
