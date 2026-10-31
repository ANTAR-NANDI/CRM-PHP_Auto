<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('system-settings.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← System Settings</a>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Location</h1>
                <p class="mt-1 text-sm text-slate-500">Build the Division → District → City → Thana → Area hierarchy.</p>
            </div>
            <button id="show-add-setting" type="button" onclick="document.getElementById('add-location-form').classList.remove('hidden'); document.getElementById('location-title').focus(); this.classList.add('hidden');" class="{{ $errors->any() ? 'hidden ' : '' }}rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Add Location</button>
        </div>
    </x-slot>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form id="add-location-form" x-data="{ type: '{{ old('location_type', 'division') }}', parent: '{{ old('parent_id') }}', parentTypes: { division: null, district: 'division', city: 'district', thana: 'city', area: 'thana' } }" method="POST" action="{{ route('system-settings.store', $settingKey) }}" class="{{ $errors->any() ? '' : 'hidden' }} border-b border-slate-100 bg-slate-50 p-5">
            @csrf
            <div class="grid max-w-4xl gap-5 md:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">Location Type
                    <select name="location_type" x-model="type" @change="parent = ''" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm">
                        @foreach($locationTypes as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">Parent
                    <select name="parent_id" x-model="parent" :disabled="type === 'division'" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm disabled:bg-slate-100">
                        <option value="">[All Locations]</option>
                        @foreach($locations as $location)<option x-show="parentTypes[type] === '{{ $location->location_type }}'" value="{{ $location->id }}" @selected(old('parent_id') == $location->id)>{{ $location->name }} ({{ ucfirst($location->location_type) }})</option>@endforeach
                    </select>
                    <span x-show="type === 'division'" class="mt-1 block text-xs font-normal text-slate-500">A division has no parent.</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700 md:col-span-2">Title
                    <input id="location-title" name="name" value="{{ old('name') }}" required placeholder="Location title" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm">
                </label>
            </div>
            <input type="hidden" name="is_active" value="1">
            <div class="mt-5 flex gap-2"><button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Save</button><button type="button" onclick="document.getElementById('add-location-form').classList.add('hidden'); document.getElementById('show-add-setting').classList.remove('hidden');" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Cancel</button></div>
        </form>

        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600"><tr><th class="px-5 py-3">Type</th><th class="px-5 py-3">Title</th><th class="px-5 py-3">Parent</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr></thead><tbody x-data="{ editingId: null }" class="divide-y divide-slate-100 bg-white">
            @forelse($locations as $location)
                <tr class="hover:bg-slate-50/70"><td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($location->location_type) }}</span></td><td class="px-5 py-4 font-semibold text-slate-800">{{ $location->name }}</td><td class="px-5 py-4 text-slate-600">{{ $location->parent_name ?: '—' }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $location->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $location->is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-3"><button type="button" @click="editingId = editingId === {{ $location->id }} ? null : {{ $location->id }}" class="text-sm font-bold text-indigo-700 hover:text-indigo-900" x-text="editingId === {{ $location->id }} ? 'Cancel' : 'Edit'"></button><form method="POST" action="{{ route('system-settings.destroy', [$settingKey, $location->id]) }}" onsubmit="return confirm('Delete this location? This cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800">Delete</button></form></div></td></tr>
                <tr x-show="editingId === {{ $location->id }}" x-cloak><td colspan="5" class="bg-slate-50 px-5 pb-5"><form method="POST" action="{{ route('system-settings.update', [$settingKey, $location->id]) }}" class="grid gap-3 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-[10rem_minmax(0,1fr)_minmax(0,1fr)_auto]">@csrf @method('PUT')<select name="location_type" class="rounded border-slate-300 py-2 text-sm">@foreach($locationTypes as $type)<option value="{{ $type }}" @selected($location->location_type === $type)>{{ ucfirst($type) }}</option>@endforeach</select><input name="name" value="{{ $location->name }}" required class="rounded border-slate-300 py-2 text-sm"><select name="parent_id" class="rounded border-slate-300 py-2 text-sm"><option value="">[All Locations]</option>@foreach($locations as $parent)<option value="{{ $parent->id }}" @selected($location->parent_id === $parent->id)>{{ $parent->name }} ({{ ucfirst($parent->location_type) }})</option>@endforeach</select><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($location->is_active)> Active</label><button class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Save changes</button></form></td></tr>
            @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">No locations added yet. Start with a division.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</x-app-layout>
