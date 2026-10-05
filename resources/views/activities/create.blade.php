<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div><h1 class="text-xl font-bold text-slate-900">New Activity</h1><p class="mt-1 text-sm text-slate-500">Record a customer interaction, follow-up, or task.</p></div>
            <a href="{{ route('activities.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Activity list</a>
        </div>
    </x-slot>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" x-data="activityForm()">
        <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-6 py-4 text-sm font-semibold text-white">ACTIVITY</div>
        <form method="POST" action="{{ route('activities.store') }}" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            <div class="grid gap-x-7 gap-y-6 md:grid-cols-2 xl:grid-cols-3">
                <label class="block text-sm font-semibold text-slate-700">Type
                    <select name="activity_type_id" x-model="typeId" @change="loadSubTypes" class="crm-select mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select activity</option>
                        @foreach($types as $type)<option value="{{ $type->id }}" @selected(old('activity_type_id') == $type->id)>{{ $type->name }}</option>@endforeach
                    </select>
                    @error('activity_type_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">Activity sub type
                    <select name="activity_sub_type_id" x-model="subTypeId" :disabled="!typeId" class="crm-select mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm disabled:bg-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" x-text="typeId ? 'Select subtype' : 'Select a type first'"></option>
                        <template x-for="subType in subTypes" :key="subType.id"><option :value="subType.id" x-text="subType.name"></option></template>
                    </select>
                    @error('activity_sub_type_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">From date &amp; time
                    <input name="from_at" type="datetime-local" value="{{ old('from_at', now()->format('Y-m-d\\TH:i')) }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('from_at')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">Activity for
                    <select name="subject_type" x-model="subjectType" @change="loadSubjects(true)" class="crm-select mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($subjectTypes as $subjectType)<option value="{{ $subjectType->key }}">{{ $subjectType->name }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">Activity with
                    <input type="hidden" name="activity_with" x-model="activityWith">
                    <select name="subject_id" x-model="subjectId" @change="setActivityWith" class="crm-select mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" x-text="subjectType ? 'Select ' + selectedSubjectName().toLowerCase() : 'Select activity for first'"></option>
                        <template x-for="subject in subjects" :key="subject.id"><option :value="subject.id" x-text="subject.label"></option></template>
                    </select>
                    <span class="mt-1 block text-xs text-slate-400">Loaded live from the selected CRM module.</span>
                    @error('subject_id')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">To date &amp; time
                    <input name="to_at" type="datetime-local" value="{{ old('to_at') }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('to_at')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700 md:col-span-2">Remarks
                    <textarea name="remarks" rows="3" placeholder="Add notes about this interaction" class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('remarks') }}</textarea>
                    @error('remarks')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">Attach image
                    <input name="attachment" type="file" accept="image/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('attachment')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
            </div>
            <label class="mt-7 flex items-center gap-2 text-sm font-medium text-slate-700"><input name="keep_todo" type="checkbox" value="1" @checked(old('keep_todo')) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Keep as a to-do</label>
            <div class="mt-7 flex gap-3 border-t border-slate-100 pt-6"><button class="rounded-md bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#176973]">Save activity</button><a href="{{ route('activities.index') }}" class="rounded-md bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Cancel</a></div>
        </form>
    </section>

    <script>
        function activityForm() {
            return {
                typeId: '{{ old('activity_type_id') }}', subTypeId: '{{ old('activity_sub_type_id') }}', subjectType: '{{ old('subject_type', $defaultSubjectType) }}', subjectId: '{{ old('subject_id', $defaultSubjectId) }}', activityWith: '{{ old('activity_with') }}', subTypes: [], subjects: [], subjectTypeNames: @json($subjectTypes->pluck('name', 'key')),
                init() { this.loadSubjects(); if (this.typeId) this.loadSubTypes(); },
                async loadSubTypes() { this.subTypeId = ''; this.subTypes = this.typeId ? await (await fetch('/crm/activity-types/' + this.typeId + '/sub-types')).json() : []; },
                async loadSubjects(clear = false) { if (clear) { this.subjectId = ''; this.activityWith = ''; } this.subjects = await (await fetch('{{ route('activities.subjects') }}?type=' + this.subjectType)).json(); },
                setActivityWith() { this.activityWith = this.subjects.find(subject => String(subject.id) === String(this.subjectId))?.label || ''; },
                selectedSubjectName() { return this.subjectTypeNames[this.subjectType] || 'record'; },
            }
        }
    </script>
</x-app-layout>
