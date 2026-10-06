<x-app-layout>
    <x-slot name="header"><h1 class="text-xl font-bold text-slate-900">Sales Collection Add</h1></x-slot>
    <form method="POST" action="{{ route('sales-collections.store') }}" x-data="{sales:{{ Js::from($sales->map(fn($sale)=>['id'=>$sale->id,'segment'=>$sale->segment_id,'dealer'=>$sale->dealer ?: 'Self','customer'=>$sale->customer_name ?: '—','saleNo'=>$sale->sale_no,'balance'=>max(0,(float)$sale->final_price-(float)$sale->paid_amount)])) }},selected:'',sale(){return this.sales.find(s=>String(s.id)===String(this.selected))||{} } }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        @csrf
        <div class="border-b px-5 py-4"><label class="mr-5 text-sm"><input type="radio" name="collection_type" value="sales" checked> Sales Collection</label><label class="text-sm"><input type="radio" name="collection_type" value="advance"> Advance/Booking</label></div>
        <div class="grid gap-5 p-5 md:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm font-semibold">Segment<select class="mt-2 block w-full rounded border-slate-300" :value="sale().segment || ''" disabled><option>[Select Segment]</option>@foreach($segments as $segment)<option value="{{ $segment->id }}">{{ $segment->name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold">Dealer<input class="mt-2 block w-full rounded border-slate-300 bg-slate-100" :value="sale().dealer || '[Select]'" readonly></label>
            <label class="text-sm font-semibold">Customer<input class="mt-2 block w-full rounded border-slate-300 bg-slate-100" :value="sale().customer || '[Select]'" readonly></label>
            <label class="text-sm font-semibold">Sales No<select required name="customer_vehicle_sale_id" x-model="selected" class="mt-2 block w-full rounded border-slate-300"><option value="">[Select]</option>@foreach($sales as $sale)<option value="{{ $sale->id }}">{{ $sale->sale_no }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold lg:col-span-2">Receive Amount<input name="amount" required type="number" min="0.01" step=".01" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold lg:col-span-2">Receive Date<input name="received_at" required type="date" value="{{ now()->format('Y-m-d') }}" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold">Balance Remaining<input class="mt-2 block w-full rounded border-slate-300 bg-slate-100" :value="sale().balance === undefined ? '' : sale().balance.toFixed(2)" readonly></label>
            <label class="text-sm font-semibold">Payment Mode<select name="payment_mode" class="mt-2 block w-full rounded border-slate-300"><option value="cash">Cash</option><option value="bank">Bank</option><option value="cheque">Cheque</option><option value="mobile_banking">Mobile Banking</option></select></label>
            <label class="text-sm font-semibold">Bank Head<input name="bank_head" class="mt-2 block w-full rounded border-slate-300" placeholder="[Select]"></label>
            <label class="text-sm font-semibold">Against Cheque <select name="cheque_id" class="mt-2 block w-full rounded border-slate-300"><option value="">[Select]</option>@foreach($cheques as $cheque)<option value="{{ $cheque->id }}">{{ $cheque->cheque_no }} — ৳{{ number_format($cheque->amount,2) }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold md:col-span-2 lg:col-span-4">Remarks<textarea name="remarks" rows="2" class="mt-2 block w-full rounded border-slate-300"></textarea></label>
        </div>
        <div class="border-t px-5 py-4"><button class="rounded bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white">Save</button><a href="{{ route('sales-collections.index') }}" class="ml-2 rounded bg-slate-100 px-5 py-2.5 text-sm">Cancel</a></div>
    </form>
</x-app-layout>
