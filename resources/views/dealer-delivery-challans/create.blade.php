<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Dealer Delivery Challan Add</h1><p class="mt-1 text-sm text-slate-500">Attach the signed delivery challan to a dealer sale.</p></div></x-slot>
    <form method="POST" enctype="multipart/form-data" action="{{ route('dealer-delivery-challans.store') }}" class="max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        <div class="grid gap-5 md:grid-cols-2">
            <label class="text-sm font-semibold md:col-span-2">Dealer Sale <select required name="dealer_sale_id" class="mt-2 block w-full rounded border-slate-300"><option value="">Select dealer sale</option>@foreach($sales as $sale)<option value="{{ $sale->id }}">{{ $sale->sale_no }} — {{ $sale->dealer_name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold">Challan No. <input required readonly name="challan_no" value="{{ $nextChallanNo }}" class="mt-2 block w-full rounded border-slate-300 bg-slate-100"></label>
            <label class="text-sm font-semibold">Delivered Date <input required type="date" name="delivered_at" value="{{ today()->format('Y-m-d') }}" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold">Delivery Point <input name="delivery_point" class="mt-2 block w-full rounded border-slate-300" placeholder="Showroom or dealer address"></label>
            <label class="text-sm font-semibold">Received By <input name="receiver_name" class="mt-2 block w-full rounded border-slate-300" placeholder="Dealer representative name"></label>
            <label class="text-sm font-semibold">Receiver Phone <input name="receiver_phone" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold">Signed Challan File <input required type="file" name="signed_file" class="mt-2 block w-full rounded border border-slate-300 bg-white text-sm file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2"></label>
            <label class="text-sm font-semibold md:col-span-2">Remarks <textarea name="remarks" rows="3" class="mt-2 block w-full rounded border-slate-300" placeholder="Vehicle/chassis handover notes"></textarea></label>
        </div>
        <div class="mt-6 flex gap-3"><button class="rounded bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white">Save Dealer Challan</button><a href="{{ route('dealer-delivery-challans.index') }}" class="rounded bg-slate-100 px-5 py-2.5 text-sm">Cancel</a></div>
    </form>
</x-app-layout>
