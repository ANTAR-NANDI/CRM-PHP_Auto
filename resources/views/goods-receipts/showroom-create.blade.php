<x-app-layout>
    <x-slot name="header">
        <div><h1 class="text-xl font-bold">Vehicle / Item Receive</h1><p class="mt-1 text-sm text-slate-500">Receive stock, record any payment made now, and create the supplier due.</p></div>
    </x-slot>

    <form method="POST" action="{{ route('goods-receipts.store') }}" x-data="receiptForm()">
        @csrf
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-200 pb-3 text-lg font-semibold">Receive &amp; Payment Information</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium">MRR Date<input name="mrr_date" type="date" value="{{ old('mrr_date', now()->format('Y-m-d')) }}" required class="mt-2 block w-full rounded border-slate-300"></label>
                <label class="text-sm font-medium">MRR No<input readonly value="{{ $nextNo }}" class="mt-2 block w-full rounded border-slate-200 bg-slate-100"></label>
                <label class="text-sm font-medium">Supplier<select name="supplier_id" required class="mt-2 block w-full rounded border-slate-300"><option value="">Select supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
                <label class="text-sm font-medium">Receive Into Showroom<select name="store_id" x-model="storeId" required class="mt-2 block w-full rounded border-slate-300"><option value="">Select showroom</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>{{ $store->name }}</option>@endforeach</select></label>
                <label class="text-sm font-medium">Payment Mode<select name="payment_mode" x-model="paymentMode" @change="if (paymentMode === 'credit') paidAmount = 0" class="mt-2 block w-full rounded border-slate-300"><option value="credit">Credit / Due</option><option value="cash">Cash</option><option value="bank">Bank</option></select></label>
                <label class="text-sm font-medium">Cash / Bank Account<select name="cash_account_id" :disabled="paymentMode === 'credit'" class="mt-2 block w-full rounded border-slate-300 disabled:bg-slate-100"><option value="">Select cash or bank account</option>@foreach($cashAccounts as $account)<option value="{{ $account->id }}" @selected(old('cash_account_id') == $account->id)>{{ $account->code }} — {{ $account->name }}</option>@endforeach</select><span class="mt-1 block text-xs text-slate-500">Required only when an amount is paid now.</span><x-input-error :messages="$errors->get('cash_account_id')" class="mt-1" /></label>
                <label class="text-sm font-medium">Paid Now<input name="paid_amount" x-model.number="paidAmount" type="number" min="0" step="0.01" value="{{ old('paid_amount', 0) }}" :readonly="paymentMode === 'credit'" class="mt-2 block w-full rounded border-slate-300 read-only:bg-slate-100"><span class="mt-1 block text-xs text-slate-500">Credit mode creates the full supplier due. Cash/Bank may be full or partial.</span><x-input-error :messages="$errors->get('paid_amount')" class="mt-1" /></label>
                <label class="text-sm font-medium">Purchase Type<select name="purchase_type" class="mt-2 block w-full rounded border-slate-300"><option value="local">Local</option><option value="import">Import</option></select></label>
                <label class="text-sm font-medium">Currency<select name="currency" class="mt-2 block w-full rounded border-slate-300"><option value="BDT">BDT</option><option value="USD">USD</option></select></label>
                <label class="text-sm font-medium">Exchange Rate<input name="exchange_rate" value="{{ old('exchange_rate', 1) }}" type="number" min="0" step=".0001" class="mt-2 block w-full rounded border-slate-300"></label>
                <label class="text-sm font-medium">PO Number<input name="po_no" value="{{ old('po_no') }}" class="mt-2 block w-full rounded border-slate-300" placeholder="Optional"></label>
                <label class="text-sm font-medium md:col-span-2">Remarks<textarea name="remarks" rows="2" class="mt-2 block w-full rounded border-slate-300">{{ old('remarks') }}</textarea></label>
            </div>
        </section>

        <section class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="bg-[#1f7d88] px-5 py-3 font-semibold text-white">Vehicles / Items Received</div>
            <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-sm"><thead class="bg-slate-50 text-left text-slate-600"><tr><th class="p-3">Showroom Position</th><th class="p-3">Item</th><th class="p-3">Quantity</th><th class="p-3">Rate</th><th class="p-3 text-right">Amount</th><th></th></tr></thead><tbody><template x-for="(line, index) in lines" :key="index"><tr class="border-t"><td class="p-2"><select x-model="line.position" :name="`items[${index}][store_position_id]`" class="w-full rounded border-slate-300"><option value="">Select position</option><template x-for="position in positions.filter(position => String(position.store_id) === String(storeId))"><option :value="position.id" x-text="position.name"></option></template></select></td><td class="p-2"><select x-model="line.product" :name="`items[${index}][product_id]`" required class="w-full rounded border-slate-300"><option value="">Select vehicle / item</option><template x-for="product in products"><option :value="product.id" x-text="`${product.part_no} — ${product.name}`"></option></template></select></td><td class="p-2"><input x-model.number="line.quantity" :name="`items[${index}][quantity]`" type="number" min="0.01" step="0.01" required class="w-full rounded border-slate-300"></td><td class="p-2"><input x-model.number="line.rate" :name="`items[${index}][rate]`" type="number" min="0" step="0.01" required class="w-full rounded border-slate-300"></td><td class="p-2 text-right font-medium" x-text="formatAmount(line.quantity * line.rate)"></td><td class="p-2"><button type="button" @click="remove(index)" x-show="lines.length > 1" class="text-rose-600">Remove</button></td></tr></template></tbody><tfoot class="border-t bg-slate-50"><tr><th colspan="4" class="p-3 text-right">Total Received</th><th class="p-3 text-right" x-text="formatAmount(total)"></th><th></th></tr><tr><th colspan="4" class="p-3 text-right">Supplier Due</th><th class="p-3 text-right text-rose-700" x-text="formatAmount(Math.max(0, total - (Number(paidAmount) || 0)))"></th><th></th></tr></tfoot></table></div>
            <button type="button" @click="add()" class="m-4 rounded bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">+ Add vehicle / item</button>
        </section>
        <div class="mt-6 flex gap-3"><button class="rounded bg-[#1f7d88] px-5 py-2.5 font-semibold text-white">Save Receive</button><a href="{{ route('goods-receipts.index') }}" class="rounded border border-slate-300 px-5 py-2.5 font-semibold text-slate-700">Cancel</a></div>
    </form>

    <script>
        function receiptForm() {
            return {
                products: @json($products), positions: @json($positions), storeId: '{{ old('store_id') }}', paymentMode: '{{ old('payment_mode', 'credit') }}', paidAmount: Number('{{ old('paid_amount', 0) }}'),
                lines: [{position: '', product: '', quantity: 1, rate: 0}],
                get total() { return this.lines.reduce((sum, line) => sum + (Number(line.quantity) || 0) * (Number(line.rate) || 0), 0) },
                add() { this.lines.push({position: '', product: '', quantity: 1, rate: 0}) },
                remove(index) { this.lines.splice(index, 1) },
                formatAmount(amount) { return `৳${Number(amount || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` }
            }
        }
    </script>
</x-app-layout>
