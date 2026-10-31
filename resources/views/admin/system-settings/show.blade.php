<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('system-settings.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← System Settings</a>
                <h1 class="mt-1 text-xl font-bold text-slate-900">{{ $setting['label'] }}</h1>
                <p class="mt-1 text-sm text-slate-500">View and manage {{ strtolower($setting['label']) }} values.</p>
            </div>
            <button id="show-add-setting" type="button" onclick="document.getElementById('add-setting-form').classList.remove('hidden'); document.getElementById('setting-name').focus(); this.classList.add('hidden');" class="{{ $errors->any() ? 'hidden ' : '' }}rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Add {{ $setting['label'] }}</button>
        </div>
    </x-slot>

    @if($settingKey === 'pipeline-stages')
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <form id="add-setting-form" method="POST" action="{{ route('system-settings.store', $settingKey) }}" class="{{ $errors->any() ? '' : 'hidden' }} grid gap-3 border-b border-slate-100 bg-slate-50 p-5 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_8rem_auto]">
                @csrf
                <input id="setting-name" name="name" value="{{ old('name') }}" required placeholder="Pipeline stage" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
                <input name="code" value="{{ old('code') }}" placeholder="Code (optional)" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
                <input name="color" type="color" value="{{ old('color', '#64748b') }}" aria-label="Stage color" class="h-10 w-full rounded-md border-slate-300 p-1">
                <input name="sort_index" type="number" min="0" max="9999" value="{{ old('sort_index', 0) }}" required placeholder="Sort index" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
                <input type="hidden" name="is_active" value="1">
                <div class="flex gap-2"><button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Save</button><button type="button" onclick="document.getElementById('add-setting-form').classList.add('hidden'); document.getElementById('show-add-setting').classList.remove('hidden');" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Cancel</button></div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr><th class="px-5 py-3">Step</th><th class="px-5 py-3">Color</th><th class="px-5 py-3">Sort Index</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody x-data="{ editingId: null }" class="divide-y divide-slate-100 bg-white">
                        @forelse($records as $record)
                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-semibold text-slate-800"><span>{{ $record->name }}</span>@if($record->code)<span class="mt-1 block text-xs font-normal text-slate-400">{{ $record->code }}</span>@endif</td>
                                <td class="px-5 py-4"><span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded-full border border-slate-300" style="background-color: {{ $record->color }}"></span><span class="font-mono text-xs text-slate-600">{{ $record->color }}</span></span></td>
                                <td class="px-5 py-4 text-slate-700">{{ $record->sort_index }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $record->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="px-5 py-4"><div class="flex justify-end gap-3"><button type="button" @click="editingId = editingId === {{ $record->id }} ? null : {{ $record->id }}" class="text-sm font-bold text-indigo-700 hover:text-indigo-900" x-text="editingId === {{ $record->id }} ? 'Cancel' : 'Edit'"></button><form method="POST" action="{{ route('system-settings.destroy', [$settingKey, $record->id]) }}" onsubmit="return confirm('Delete this pipeline stage? This cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800">Delete</button></form></div></td>
                            </tr>
                            <tr x-show="editingId === {{ $record->id }}" x-cloak>
                                <td colspan="5" class="bg-slate-50 px-5 pb-5">
                                    <form method="POST" action="{{ route('system-settings.update', [$settingKey, $record->id]) }}" class="grid gap-3 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_8rem_auto]">
                                        @csrf @method('PUT')
                                        <input name="name" value="{{ $record->name }}" required class="w-full rounded border-slate-300 py-2 text-sm">
                                        <input name="code" value="{{ $record->code }}" placeholder="Code (optional)" class="w-full rounded border-slate-300 py-2 text-sm">
                                        <input name="color" type="color" value="{{ $record->color }}" aria-label="Stage color" class="h-10 w-full rounded border-slate-300 p-1">
                                        <input name="sort_index" type="number" min="0" max="9999" value="{{ $record->sort_index }}" required class="w-full rounded border-slate-300 py-2 text-sm">
                                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label>
                                        <button class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Save changes</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">No lead pipeline stages added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form id="add-setting-form" method="POST" action="{{ route('system-settings.store', $settingKey) }}" class="{{ $errors->any() ? '' : 'hidden' }} grid gap-3 border-b border-slate-100 bg-slate-50 p-5 md:grid-cols-[1fr_1fr_auto]">
            @csrf
            <input id="setting-name" name="name" required placeholder="{{ $setting['label'] }} name" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
            @if($setting['code'])
                <input name="code" placeholder="Code (optional)" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
            @else
                <span class="hidden md:block"></span>
            @endif
            @if($setting['points'] ?? false)
                <input name="point" type="number" min="0" max="100" value="{{ old('point', 1) }}" required placeholder="Points" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
            @endif
            <input type="hidden" name="is_active" value="1">
            <div class="flex gap-2"><button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Save</button><button type="button" onclick="document.getElementById('add-setting-form').classList.add('hidden'); document.getElementById('show-add-setting').classList.remove('hidden');" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Cancel</button></div>
        </form>

        <div class="divide-y divide-slate-100">
            @forelse($records as $record)
                <div x-data="{ editing: false }" class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $record->name }}</p>
                            @if($setting['code'])
                                <p class="mt-1 text-xs text-slate-400">{{ $record->code ?: 'No code' }}</p>
                            @endif
                            @if($setting['points'] ?? false)
                                <p class="mt-1 text-xs font-semibold text-teal-700">{{ $record->point }} points</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $record->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span>
                            <button type="button" @click="editing = !editing" class="text-sm font-bold text-indigo-700 hover:text-indigo-900" x-text="editing ? 'Cancel' : 'Edit'"></button>
                            <form method="POST" action="{{ route('system-settings.destroy', [$settingKey, $record->id]) }}" onsubmit="return confirm('Delete this {{ strtolower($setting['label']) }}? This cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800">Delete</button></form>
                        </div>
                    </div>
                    <form x-cloak x-show="editing" method="POST" action="{{ route('system-settings.update', [$settingKey, $record->id]) }}" class="mt-4 grid gap-3 rounded-lg bg-slate-50 p-4 md:grid-cols-[1fr_1fr_auto]">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $record->name }}" required class="w-full rounded border-slate-300 py-2 text-sm">
                        @if($setting['code'])
                            <input name="code" value="{{ $record->code }}" class="w-full rounded border-slate-300 py-2 text-sm">
                        @else
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label>
                        @endif
                        @if($setting['points'] ?? false)
                            <input name="point" type="number" min="0" max="100" value="{{ $record->point }}" required class="w-full rounded border-slate-300 py-2 text-sm">
                        @endif
                        @if($setting['code'])
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label>
                        @endif
                        <button class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Save changes</button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">No {{ strtolower($setting['label']) }} values added yet.</div>
            @endforelse
        </div>
    </section>
    @endif
</x-app-layout>
