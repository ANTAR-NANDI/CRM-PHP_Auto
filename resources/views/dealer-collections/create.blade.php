<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Dealer Collection Entry</h1><p class="mt-1 text-sm text-slate-500">Record a payment received from a wholesale dealer.</p></div></x-slot>
    <form method="POST" action="{{ route('dealer-collections.store') }}" x-data="{ sales: {{ Js::from($sales) }}, selected: '', sale(){ return this.sales.find(item => String(item.id) === String(this.selected)) || {} } }" class="max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        <div class="grid gap-5 md:grid-cols-2">
            <label class="text-sm font-semibold">Dealer Sale <select required name="dealer_sale_id" x-model="selected" class="mt-2 block w-full rounded border-slate-300"><option value="">Select outstanding dealer sale</option>@foreach($sales as $sale)<option value="{{ $sale->id }}">{{ $sale->sale_no }} — {{ $sale->dealer_name }} (Due ৳{{ number_format($sale->total_amount - $sale->received_amount, 2) }})</option>@endforeach</select></label>
            <label class="text-sm font-semibold">Outstanding Balance <input readonly :value="sale().id ? Number(sale().total_amount - sale().received_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : ''" class="mt-2 block w-full rounded border-slate-300 bg-slate-100"></label>
            <label class="text-sm font-semibold">Receive Amount <input required name="amount" type="number" min="0.01" step=".01" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold">Receive Date <input required name="received_at" type="date" value="{{ today()->format('Y-m-d') }}" class="mt-2 block w-full rounded border-slate-300"></label>
            <label class="text-sm font-semibold">Payment Mode <select required name="payment_mode" class="mt-2 block w-full rounded border-slate-300"><option value="cash">Cash</option><option value="bank">Bank</option><option value="cheque">Cheque</option><option value="mobile_banking">Mobile Banking</option></select></label>
            <label class="text-sm font-semibold">Cash / Bank Head <select required name="cash_account_id" class="mt-2 block w-full rounded border-slate-300"><option value="">Select account</option>@foreach($cashAccounts as $account)<option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold md:col-span-2">Remarks <textarea name="remarks" rows="3" class="mt-2 block w-full rounded border-slate-300" placeholder="Payment reference, cheque number, or note"></textarea></label>
        </div>
        <div class="mt-6 flex gap-3"><button class="rounded bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white">Save Dealer Collection</button><a href="{{ route('dealer-collections.index') }}" class="rounded bg-slate-100 px-5 py-2.5 text-sm">Cancel</a></div>
    </form>
</x-app-layout>
