<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    private const SETTINGS = [
        'lead-sources' => ['label' => 'Lead Source', 'table' => 'crm_sources', 'code' => false],
        'pipeline-stages' => ['label' => 'Lead Pipeline Stage', 'table' => 'crm_pipelines', 'code' => true],
        'departments' => ['label' => 'Department', 'table' => 'departments', 'code' => true],
    ];

    public function index(): View
    {
        return view('admin.system-settings.index', [
            'settings' => self::SETTINGS,
            'records' => collect(self::SETTINGS)->mapWithKeys(fn (array $setting, string $key) => [$key => DB::table($setting['table'])->orderBy('name')->get()]),
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
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        if ($meta['code']) {
            $data['code'] = $data['code'] ?: null;
        }

        return $data;
    }
}
