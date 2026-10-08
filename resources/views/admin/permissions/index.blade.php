<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">System Settings</p><h1 class="mt-1 text-2xl font-bold text-slate-900">Permission Setup</h1><p class="mt-1 text-sm text-slate-500">Create the permissions that can be assigned to user roles.</p></div>
            <button id="show-add-permission" type="button" onclick="document.getElementById('add-permission-form').classList.remove('hidden'); document.getElementById('permission-name').focus(); this.classList.add('hidden');" class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Add Permission</button>
        </div>
    </x-slot>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form id="add-permission-form" method="POST" action="{{ route('permissions.store') }}" class="{{ $errors->any() ? '' : 'hidden' }} grid gap-3 border-b border-slate-100 bg-slate-50 p-5 md:grid-cols-3">
            @csrf
            <input id="permission-name" name="name" value="{{ old('name') }}" required placeholder="Permission, e.g. leads.manage" class="rounded-md border-slate-300 py-2.5 text-sm">
            <input name="module_name" value="{{ old('module_name') }}" required placeholder="Module, e.g. Customer Relationship" class="rounded-md border-slate-300 py-2.5 text-sm">
            <input name="submodule_name" value="{{ old('submodule_name') }}" required placeholder="Sub-module, e.g. Leads" class="rounded-md border-slate-300 py-2.5 text-sm">
            <div class="flex gap-2"><button class="rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">Save</button><button type="button" onclick="document.getElementById('add-permission-form').classList.add('hidden'); document.getElementById('show-add-permission').classList.remove('hidden');" class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Cancel</button></div>
            <x-input-error :messages="$errors->get('name')" class="md:col-span-3" />
        </form>

        <div class="divide-y divide-slate-100">
            @forelse($permissions as $permission)
                <div x-data="{ editing: false }" class="flex flex-wrap items-center justify-between gap-4 p-5">
                    <div><p class="font-mono font-semibold text-slate-800">{{ $permission->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $permission->module_name ?: 'Other' }} · {{ $permission->submodule_name ?: 'General' }} · Assigned to {{ $permission->roles_count }} role(s)</p></div>
                    <div class="flex items-center gap-3"><button type="button" @click="editing = !editing" class="text-sm font-bold text-indigo-700" x-text="editing ? 'Cancel' : 'Edit'"></button><form method="POST" action="{{ route('permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this unused permission?');">@csrf @method('DELETE')<button class="text-sm font-bold text-red-600">Delete</button></form></div>
                    <form x-cloak x-show="editing" method="POST" action="{{ route('permissions.update', $permission) }}" class="grid w-full gap-3 rounded-lg bg-slate-50 p-4 md:grid-cols-4">@csrf @method('PUT')<input name="name" value="{{ $permission->name }}" required class="min-w-0 rounded border-slate-300 py-2 text-sm"><input name="module_name" value="{{ $permission->module_name }}" required placeholder="Module" class="min-w-0 rounded border-slate-300 py-2 text-sm"><input name="submodule_name" value="{{ $permission->submodule_name }}" required placeholder="Sub-module" class="min-w-0 rounded border-slate-300 py-2 text-sm"><button class="rounded bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Save changes</button></form>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">No permissions have been added yet.</div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $permissions->links() }}</div>
    </section>
</x-app-layout>
