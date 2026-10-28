<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Delivery Challan Add</h1>
            <p class="mt-1 text-sm text-slate-500">Only sales cleared by Accounts can receive a delivery challan.</p>
        </div>
    </x-slot>

    <form method="POST" enctype="multipart/form-data" action="{{ route('sales-documents.store') }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="space-y-5">
            <label class="block text-sm font-semibold">Customer Sale <span class="text-rose-600">*</span>
                <select name="customer_vehicle_sale_id" required class="mt-2 block w-full rounded border-slate-300">
                    <option value="">Select Accounts-cleared sale</option>
                    @foreach($sales as $sale)
                        <option value="{{ $sale->id }}" @selected(old('customer_vehicle_sale_id', $selectedSaleId) == $sale->id)>{{ $sale->sale_no }} — {{ $sale->customer_name ?: 'Walk-in customer' }} ({{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }})</option>
                    @endforeach
                </select>
            </label>
            @if($sales->isEmpty())<p class="-mt-3 text-sm text-amber-700">There are no Accounts-cleared sales ready for a challan.</p>@endif
            <label class="block text-sm font-semibold">Challan title / no. <span class="text-rose-600">*</span><input name="title" value="{{ old('title') }}" required placeholder="Example: DC-CVS-261011-0001" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="block text-sm font-semibold">Signed challan file <span class="text-rose-600">*</span><input name="file" type="file" required class="mt-2 block w-full cursor-pointer rounded border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold"></label>
            <p class="-mt-3 text-xs text-slate-500">Upload the signed PDF, image, or other supporting file. Maximum size: 10 MB.</p>
            <label class="block text-sm font-semibold">Remarks<textarea name="remarks" rows="5" class="mt-2 block w-full rounded border-slate-300">{{ old('remarks') }}</textarea></label>
        </div>
        <div class="mt-6 flex gap-3"><button class="rounded bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white">Save Challan</button><a href="{{ route('sales-documents.index') }}" class="rounded bg-slate-100 px-5 py-2.5 text-sm">Cancel</a></div>
    </form>
</x-app-layout>
