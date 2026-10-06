<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">System Settings</h1><p class="mt-1 text-sm text-slate-500">Maintain the dynamic dropdown values used throughout CRM and operations.</p></div></x-slot>

    <div class="mb-6 grid gap-3 sm:grid-cols-3">
        <a href="{{ route('activity-setup.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300"><p class="font-bold text-slate-800">Activity Type</p><p class="mt-1 text-xs text-slate-500">Manage activity types and sub-types</p></a>
        <a href="{{ route('products.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300"><p class="font-bold text-slate-800">Products</p><p class="mt-1 text-xs text-slate-500">Manage product master data</p></a>
        <a href="{{ route('stores.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300"><p class="font-bold text-slate-800">Showroom / Warehouse</p><p class="mt-1 text-xs text-slate-500">Manage store and outlet locations</p></a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        @foreach($settings as $key => $setting)
            <section id="{{ $key }}" class="scroll-mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-4"><h2 class="font-bold text-slate-800">{{ $setting['label'] }}</h2></div>
                <form method="POST" action="{{ route('system-settings.store', $key) }}" class="space-y-3 border-b border-slate-100 p-5">@csrf
                    <input name="name" required placeholder="{{ $setting['label'] }} name" class="block w-full rounded-md border-slate-300 py-2.5 text-sm">
                    @if($setting['code'])<input name="code" placeholder="Code (optional)" class="block w-full rounded-md border-slate-300 py-2.5 text-sm">@endif
                    <input type="hidden" name="is_active" value="1"><button class="rounded-md bg-[#1f7d88] px-4 py-2 text-sm font-semibold text-white">Add</button>
                </form>
                <div class="divide-y divide-slate-100">@forelse($records[$key] as $record)<div x-data="{ editing: false }" class="p-4"><div class="flex items-center justify-between gap-3"><div><p class="font-semibold text-slate-800">{{ $record->name }}</p>@if($setting['code'])<p class="text-xs text-slate-400">{{ $record->code ?: 'No code' }}</p>@endif</div><div class="flex items-center gap-2"><span class="rounded-full px-2 py-1 text-xs {{ $record->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span><button type="button" @click="editing = !editing" class="text-xs font-bold text-indigo-700">Edit</button></div></div><form x-cloak x-show="editing" method="POST" action="{{ route('system-settings.update', [$key, $record->id]) }}" class="mt-3 space-y-2 rounded-md bg-slate-50 p-3">@csrf @method('PUT')<input name="name" value="{{ $record->name }}" required class="block w-full rounded border-slate-300 py-2 text-sm">@if($setting['code'])<input name="code" value="{{ $record->code }}" class="block w-full rounded border-slate-300 py-2 text-sm">@endif<label class="flex items-center gap-2 text-xs"><input type="checkbox" name="is_active" value="1" @checked($record->is_active)> Active</label><button class="text-xs font-bold text-slate-700">Save changes</button></form></div>@empty<div class="p-6 text-sm text-slate-500">No values added yet.</div>@endforelse</div>
            </section>
        @endforeach
    </div>
</x-app-layout>
