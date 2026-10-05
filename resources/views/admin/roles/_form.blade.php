@csrf
@if(isset($editing) && $editing) @method('PUT') @endif
<div class="space-y-6">
    <div class="max-w-xl">
        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Role name <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $role->name) }}" placeholder="Example: stock-officer" {{ $role->name === 'admin' ? 'readonly' : '' }} class="block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $role->name === 'admin' ? 'bg-slate-100' : '' }}">
        <p class="mt-2 text-xs text-slate-500">Use lowercase letters, numbers, and hyphens only. This name appears in the employee role dropdown.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div x-data="permissionMatrix()" x-init="syncAll()" class="rounded-xl border border-slate-200 bg-slate-50 p-5">
        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><h2 class="font-bold text-slate-800">Module &amp; sub-module permissions</h2><p class="mt-1 text-sm text-slate-500">Select a module to allow all of its sub-modules, or choose permissions individually.</p></div><button type="button" @click="setAll(true)" class="rounded-md border border-indigo-200 bg-white px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-50">Select all</button></div>
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($permissions as $module => $subModules)
                @php($moduleKey = \Illuminate\Support\Str::slug($module))
                <section class="rounded-lg border border-slate-200 bg-white p-4"><label class="mb-4 flex cursor-pointer items-center gap-3 border-b border-slate-100 pb-3"><input type="checkbox" data-module="{{ $moduleKey }}" @change="toggleModule('{{ $moduleKey }}', $event.target.checked)" class="module-check rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span class="text-sm font-bold text-indigo-700">{{ $module }}</span></label><div class="space-y-4">@foreach($subModules as $subModule => $modulePermissions) @php($subKey = $moduleKey.'-'.\Illuminate\Support\Str::slug($subModule))<div><label class="mb-2 flex cursor-pointer items-center gap-2"><input type="checkbox" data-submodule="{{ $subKey }}" @change="toggleSubModule('{{ $subKey }}', $event.target.checked)" class="submodule-check rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span class="text-xs font-bold uppercase tracking-wide text-slate-600">{{ $subModule }}</span></label><div class="space-y-2 border-l border-slate-100 pl-5">@foreach($modulePermissions as $permission)<label class="flex cursor-pointer items-start gap-3 rounded-md py-1 hover:bg-slate-50"><input class="permission-check mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" data-module="{{ $moduleKey }}" data-submodule="{{ $subKey }}" @change="syncAll()" type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $selectedPermissions), true))><span><span class="block text-sm font-semibold text-slate-700">{{ ucfirst(str_replace(['.', '-'], [' ', ' '], $permission->name)) }}</span><span class="text-xs text-slate-400">{{ $permission->name }}</span></span></label>@endforeach</div></div>@endforeach</div></section>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('permissions')" class="mt-3" />
    </div>
    <div class="flex justify-end gap-3"><a href="{{ route('roles.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</a><button class="rounded-lg bg-gradient-to-r from-[#4165dd] to-[#4432f2] px-5 py-2.5 text-sm font-bold text-white shadow hover:opacity-95">{{ isset($editing) && $editing ? 'Update Role & Permissions' : 'Create Role' }}</button></div>
</div>
<script>
function permissionMatrix() { return {
    children(selector) { return [...this.$root.querySelectorAll(selector)]; },
    setAll(checked) { this.children('.permission-check').forEach(item => item.checked = checked); this.syncAll(); },
    toggleModule(module, checked) { this.children(`.permission-check[data-module="${module}"]`).forEach(item => item.checked = checked); this.syncAll(); },
    toggleSubModule(subModule, checked) { this.children(`.permission-check[data-submodule="${subModule}"]`).forEach(item => item.checked = checked); this.syncAll(); },
    syncAll() { this.children('.submodule-check').forEach(box => this.syncBox(box, `.permission-check[data-submodule="${box.dataset.submodule}"]`)); this.children('.module-check').forEach(box => this.syncBox(box, `.permission-check[data-module="${box.dataset.module}"]`)); },
    syncBox(box, selector) { const items = this.children(selector); const selected = items.filter(item => item.checked).length; box.checked = items.length > 0 && selected === items.length; box.indeterminate = selected > 0 && selected < items.length; }
}; }
</script>
