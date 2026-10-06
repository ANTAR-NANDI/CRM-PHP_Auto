<x-app-layout>
    <x-slot name="header">
        <div><h1 class="text-xl font-bold text-slate-900">CRM Activity Setup</h1><p class="mt-1 text-sm text-slate-500">Maintain the dropdown values used by the Activity form.</p></div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <section class="space-y-6">
            <div id="sub-types" class="scroll-mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-bold text-slate-800">Activity type</h2>
                <form method="POST" action="{{ route('activity-setup.types.store') }}" class="mt-4 space-y-4">@csrf
                    <label class="block text-sm font-semibold text-slate-700">Type name<input name="name" required placeholder="Example: Facebook" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>
                    <button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white">Add type</button>
                </form>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-bold text-slate-800">Activity sub type</h2>
                <form method="POST" action="{{ route('activity-setup.sub-types.store') }}" class="mt-4 space-y-4">@csrf
                    <label class="block text-sm font-semibold text-slate-700">Activity type<select name="activity_type_id" required class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"><option value="">Select type</option>@foreach($types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></label>
                    <label class="block text-sm font-semibold text-slate-700">Sub type name<input name="name" required placeholder="Example: Lead form" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>
                    <button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white">Add sub type</button>
                </form>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-bold text-slate-800">Activity For category</h2>
                <p class="mt-1 text-xs text-slate-500">Each category is connected to its live CRM records for the Activity With dropdown.</p>
                <form method="POST" action="{{ route('activity-setup.subject-types.store') }}" class="mt-4 space-y-4">@csrf
                    <label class="block text-sm font-semibold text-slate-700">Display name<input name="name" required placeholder="Example: Customer" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>
                    <label class="block text-sm font-semibold text-slate-700">CRM module<select name="key" required class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"><option value="">Select module</option><option value="customer">Customer</option><option value="lead">Lead</option><option value="contact">Contact</option><option value="organization">Organization</option></select></label>
                    <button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white">Add category</button>
                </form>
            </div>
        </section>

        <section class="space-y-6">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4"><h2 class="font-bold text-slate-800">Type and sub type hierarchy</h2></div>
                <div class="divide-y divide-slate-100">@forelse($types as $type)
                    <div class="p-5" x-data="{ editing: false }">
                        <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="font-bold text-slate-800">{{ $type->name }}</p><p class="mt-1 text-xs {{ $type->is_active ? 'text-emerald-700' : 'text-slate-400' }}">{{ $type->is_active ? 'Active' : 'Inactive' }} · {{ $type->subTypes->count() }} sub type(s)</p></div><div class="flex gap-2"><button type="button" @click="editing = !editing" class="rounded border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700">Edit</button><form method="POST" action="{{ route('activity-setup.types.destroy', $type) }}" onsubmit="return confirm('Delete this unused activity type and its sub types?')">@csrf @method('DELETE')<button class="rounded border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700">Delete</button></form></div></div>
                        <form x-show="editing" method="POST" action="{{ route('activity-setup.types.update', $type) }}" class="mt-4 flex flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3">@csrf @method('PUT')<input name="name" value="{{ $type->name }}" class="rounded border-slate-300 py-2 text-sm"><label class="text-sm"><input name="is_active" type="checkbox" value="1" @checked($type->is_active)> Active</label><button class="rounded bg-slate-800 px-3 py-2 text-xs font-semibold text-white">Update</button></form>
                        <div class="mt-4 grid gap-2 md:grid-cols-2">@forelse($type->subTypes as $subType)<div x-data="{ editing: false }" class="rounded border border-slate-100 px-3 py-2"><div class="flex items-center justify-between gap-2"><div><p class="text-sm font-medium text-slate-700">{{ $subType->name }}</p><p class="text-xs {{ $subType->is_active ? 'text-emerald-700' : 'text-slate-400' }}">{{ $subType->is_active ? 'Active' : 'Inactive' }}</p></div><div class="flex gap-2"><button type="button" @click="editing = !editing" class="text-xs font-semibold text-indigo-700">Edit</button><form method="POST" action="{{ route('activity-setup.sub-types.destroy', $subType) }}">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-700">Delete</button></form></div></div><form x-show="editing" method="POST" action="{{ route('activity-setup.sub-types.update', $subType) }}" class="mt-2 flex gap-2">@csrf @method('PUT')<input name="name" value="{{ $subType->name }}" class="min-w-0 flex-1 rounded border-slate-300 py-1 text-sm"><label class="text-xs"><input name="is_active" type="checkbox" value="1" @checked($subType->is_active)> Active</label><button class="text-xs font-semibold text-slate-700">Save</button></form></div>@empty<p class="text-sm text-slate-400">No sub types yet.</p>@endforelse</div>
                    </div>
                @empty<div class="px-6 py-12 text-center text-slate-500">No activity types found. Add the first one.</div>@endforelse</div>
            </div>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4"><h2 class="font-bold text-slate-800">Activity For categories</h2></div>
                <div class="divide-y divide-slate-100">@forelse($subjectTypes as $subjectType)<div class="flex flex-wrap items-center justify-between gap-4 p-4" x-data="{ editing: false }"><div><p class="font-semibold text-slate-800">{{ $subjectType->name }}</p><p class="text-xs text-slate-500">CRM module: {{ ucfirst($subjectType->key) }} · <span class="{{ $subjectType->is_active ? 'text-emerald-700' : 'text-slate-400' }}">{{ $subjectType->is_active ? 'Active' : 'Inactive' }}</span></p></div><div class="flex gap-2"><button type="button" @click="editing = !editing" class="rounded border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700">Edit</button><form method="POST" action="{{ route('activity-setup.subject-types.destroy', $subjectType) }}">@csrf @method('DELETE')<button class="rounded border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700">Delete</button></form></div><form x-show="editing" method="POST" action="{{ route('activity-setup.subject-types.update', $subjectType) }}" class="flex w-full flex-wrap items-center gap-3 rounded-lg bg-slate-50 p-3">@csrf @method('PUT')<input name="name" value="{{ $subjectType->name }}" class="rounded border-slate-300 py-2 text-sm"><label class="text-sm"><input name="is_active" type="checkbox" value="1" @checked($subjectType->is_active)> Active</label><button class="rounded bg-slate-800 px-3 py-2 text-xs font-semibold text-white">Update</button></form></div>@empty<div class="px-6 py-8 text-sm text-slate-500">No categories yet. Add a CRM module above.</div>@endforelse</div>
            </div>
        </section>
    </div>
</x-app-layout>
