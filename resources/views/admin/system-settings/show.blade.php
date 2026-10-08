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

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form id="add-setting-form" method="POST" action="{{ route('system-settings.store', $settingKey) }}" class="{{ $errors->any() ? '' : 'hidden' }} grid gap-3 border-b border-slate-100 bg-slate-50 p-5 md:grid-cols-[1fr_1fr_auto]">
            @csrf
            <input id="setting-name" name="name" required placeholder="{{ $setting['label'] }} name" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
            @if($setting['code'])
                <input name="code" placeholder="Code (optional)" class="w-full rounded-md border-slate-300 py-2.5 text-sm">
            @else
                <span class="hidden md:block"></span>
            @endif
            <input type="hidden" name="is_active" value="1">
            <div class="flex gap-2"><button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Save</button><button type="button" onclick="document.getElementById('add-setting-form').classList.add('hidden'); document.getElementById('show-add-setting').classList.remove('hidden');" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Cancel</button></div>
        </form>

        <div class="divide-y divide-slate-100">
            @forelse($records as $record)
                <div x-data="{ editing: false }" class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div><p class="font-semibold text-slate-800">{{ $record->name }}</p>@if($setting['code'])<p class="mt-1 text-xs text-slate-400">{{ $record->code ?: 'No code' }}</p>@endif</div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $record->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span>
                            <button type="button" @click="editing = !editing" class="text-sm font-bold text-indigo-700 hover:text-indigo-900" x-text="editing ? 'Cancel' : 'Edit'"></button>
                            <form method="POST" action="{{ route('system-settings.destroy', [$settingKey, $record->id]) }}" onsubmit="return confirm('Delete this {{ strtolower($setting['label']) }}? This cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800">Delete</button></form>
                        </div>
                    </div>
                    <form x-cloak x-show="editing" method="POST" action="{{ route('system-settings.update', [$settingKey, $record->id]) }}" class="mt-4 grid gap-3 rounded-lg bg-slate-50 p-4 md:grid-cols-[1fr_1fr_auto]">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $record->name }}" required class="w-full rounded border-slate-300 py-2 text-sm">
                        @if($setting['code'])<input name="code" value="{{ $record->code }}" class="w-full rounded border-slate-300 py-2 text-sm">@else<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label>@endif
                        @if($setting['code'])<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label>@endif
                        <button class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Save changes</button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">No {{ strtolower($setting['label']) }} values added yet.</div>
            @endforelse
        </div>
    </section>
</x-app-layout>
