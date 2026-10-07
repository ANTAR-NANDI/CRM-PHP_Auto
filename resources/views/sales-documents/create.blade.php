<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-bold text-slate-900">Delivery Challan Upload</h1></x-slot>
    <form method="POST" enctype="multipart/form-data" action="{{ route('sales-documents.store') }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="space-y-5">
            <label class="block text-sm font-semibold">Challan title / no.<input name="title" value="{{ old('title') }}" required class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="block text-sm font-semibold">Choose File<input name="file" type="file" required class="mt-2 block w-full cursor-pointer rounded border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold"></label>
            <p class="-mt-3 text-xs text-slate-500">Maximum file size: 10 MB</p>
            <label class="block text-sm font-semibold">Remarks<textarea name="remarks" rows="5" class="mt-2 block w-full rounded border-slate-300">{{ old('remarks') }}</textarea></label>
        </div>
        <div class="mt-6 flex gap-3"><button class="rounded bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white">Save</button><a href="{{ route('sales-documents.index') }}" class="rounded bg-slate-100 px-5 py-2.5 text-sm">Cancel</a></div>
    </form>
</x-app-layout>
