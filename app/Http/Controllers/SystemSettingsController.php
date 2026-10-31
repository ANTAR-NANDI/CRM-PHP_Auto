<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    private const SETTINGS = [
        'lead-sources' => ['label' => 'Lead Source', 'table' => 'crm_sources', 'code' => false],
        'pipeline-stages' => ['label' => 'Lead Pipeline Stage', 'table' => 'crm_pipelines', 'code' => true, 'color' => true, 'sortIndex' => true],
        'segments' => ['label' => 'Segment', 'table' => 'crm_segments', 'code' => false],
        'lead-statuses' => ['label' => 'Lead Status', 'table' => 'crm_lead_statuses', 'code' => false, 'points' => true],
        'vehicle-colors' => ['label' => 'Vehicle Color', 'table' => 'crm_colors', 'code' => false],
        'event-types' => ['label' => 'Event Type', 'table' => 'event_types', 'code' => false],
        'notification-types' => ['label' => 'Notification Type', 'table' => 'notification_types', 'code' => false],
        'email-settings' => ['label' => 'Email Setting', 'table' => 'email_settings', 'code' => false],
        'todo-types' => ['label' => 'Todo Type', 'table' => 'todo_types', 'code' => false],
        'locations' => ['label' => 'Location', 'table' => 'crm_locations', 'code' => false, 'hierarchy' => true],
        'contact-types' => ['label' => 'Contact Type', 'table' => 'crm_contact_types', 'code' => false],
        'departments' => ['label' => 'Department', 'table' => 'departments', 'code' => true],
    ];

    private const LOCATION_TYPES = ['division', 'district', 'city', 'thana', 'area'];

    public function index(): View
    {
        return view('admin.system-settings.index', [
            'settings' => self::SETTINGS,
            'records' => collect(self::SETTINGS)->mapWithKeys(fn (array $setting, string $key) => [$key => DB::table($setting['table'])->orderBy('name')->get()]),
        ]);
    }

    public function show(string $setting): View
    {
        $meta = $this->setting($setting);

        if ($meta['hierarchy'] ?? false) {
            return view('admin.system-settings.locations', [
                'settingKey' => $setting,
                'setting' => $meta,
                'locationTypes' => self::LOCATION_TYPES,
                'locations' => DB::table('crm_locations as location')
                    ->leftJoin('crm_locations as parent', 'location.parent_id', '=', 'parent.id')
                    ->select('location.*', 'parent.name as parent_name')
                    ->orderByRaw("FIELD(location.location_type, 'division', 'district', 'city', 'thana', 'area')")
                    ->orderBy('location.name')
                    ->get(),
            ]);
        }

        return view('admin.system-settings.show', [
            'settingKey' => $setting,
            'setting' => $meta,
            'records' => DB::table($meta['table'])
                ->when($meta['sortIndex'] ?? false, fn ($query) => $query->orderBy('sort_index'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, string $setting): RedirectResponse
    {
        $meta = $this->setting($setting);
        $data = $this->validated($request, $meta);
        DB::table($meta['table'])->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', $meta['label'].' added successfully.');
    }

    public function update(Request $request, string $setting, int $id): RedirectResponse
    {
        $meta = $this->setting($setting);
        abort_unless(DB::table($meta['table'])->where('id', $id)->exists(), 404);
        $data = $this->validated($request, $meta, $id);
        DB::table($meta['table'])->where('id', $id)->update($data + ['updated_at' => now()]);

        return back()->with('success', $meta['label'].' updated successfully.');
    }

    public function destroy(string $setting, int $id): RedirectResponse
    {
        $meta = $this->setting($setting);
        abort_unless(DB::table($meta['table'])->where('id', $id)->exists(), 404);

        try {
            DB::table($meta['table'])->where('id', $id)->delete();
        } catch (QueryException) {
            return back()->with('error', 'This '.$meta['label'].' cannot be deleted because it is still in use.');
        }

        return back()->with('success', $meta['label'].' deleted successfully.');
    }

    private function setting(string $key): array
    {
        abort_unless(array_key_exists($key, self::SETTINGS), 404);

        return self::SETTINGS[$key];
    }

    private function validated(Request $request, array $meta, ?int $id = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique($meta['table'], 'name')->ignore($id)],
            'is_active' => ['nullable', 'boolean'],
        ];
        if ($meta['code']) {
            $rules['code'] = ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique($meta['table'], 'code')->ignore($id)];
        }
        if ($meta['points'] ?? false) {
            $rules['point'] = ['required', 'integer', 'min:0', 'max:100'];
        }
        if ($meta['color'] ?? false) {
            $rules['color'] = ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'];
        }
        if ($meta['sortIndex'] ?? false) {
            $rules['sort_index'] = ['required', 'integer', 'min:0', 'max:9999'];
        }
        if ($meta['hierarchy'] ?? false) {
            $rules['location_type'] = ['required', Rule::in(self::LOCATION_TYPES)];
            $rules['parent_id'] = ['nullable', 'exists:crm_locations,id'];
        }
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        if ($meta['code']) {
            $data['code'] = $data['code'] ?: null;
        }
        if ($meta['color'] ?? false) {
            $data['color'] = $data['color'] ?: '#64748b';
        }
        if ($meta['hierarchy'] ?? false) {
            $parentTypes = ['division' => null, 'district' => 'division', 'city' => 'district', 'thana' => 'city', 'area' => 'thana'];
            $expectedType = $parentTypes[$data['location_type']];

            $parentId = $data['parent_id'] ?? null;

            if ($id && (int) $parentId === $id) {
                throw ValidationException::withMessages(['parent_id' => 'A location cannot be its own parent.']);
            }
            if ($expectedType === null && $parentId) {
                throw ValidationException::withMessages(['parent_id' => 'A division cannot have a parent location.']);
            }
            if ($expectedType !== null && $parentId) {
                $parentType = DB::table('crm_locations')->where('id', $parentId)->value('location_type');
                if ($parentType !== $expectedType) {
                    throw ValidationException::withMessages(['parent_id' => 'Select a '.ucfirst($expectedType).' as the parent for this '.ucfirst($data['location_type']).'.']);
                }
            }
        }

        return $data;
    }
}
