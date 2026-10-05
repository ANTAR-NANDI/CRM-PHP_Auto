<x-app-layout>
    <x-slot name="header"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 class="text-xl font-bold text-slate-900">Purchase Invoice</h1><p class="mt-1 text-sm text-slate-500">{{ $purchase->invoice_number }}</p></div><div class="flex flex-wrap gap-2 print:hidden"><button type="button" onclick="downloadPurchaseInvoice()" class="rounded-md bg-[#203D9F] px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-[#19327f]">↓ Download PDF</button><a href="{{ route('purchases.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Purchase List</a></div></div></x-slot>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-5 bg-gradient-to-r from-[#0a185d] to-[#2748bd] p-6 text-white sm:flex-row"><div><p class="text-xs font-bold uppercase tracking-[.2em] text-indigo-200">Purchase invoice</p><h2 class="mt-2 text-2xl font-black">{{ $purchase->invoice_number }}</h2></div><div class="sm:text-right"><p class="text-sm text-indigo-200">Purchase date</p><p class="mt-1 font-bold">{{ $purchase->purchased_at->format('d F Y') }}</p></div></div>
        <div class="grid gap-5 border-b border-slate-200 p-6 sm:grid-cols-2"><div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Supplier</p><p class="mt-2 text-lg font-bold text-slate-800">{{ $purchase->supplier->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $purchase->supplier->phone ?: 'No phone' }}</p></div><div class="sm:text-right"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Received by</p><p class="mt-2 font-bold text-slate-800">{{ $purchase->user->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $purchase->created_at->format('d M Y, h:i A') }}</p></div></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[1100px] text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Medicine</th><th class="px-5 py-3.5">Batch / Expiry</th><th class="px-5 py-3.5 text-right">Received</th><th class="px-5 py-3.5 text-right">Stock Pieces</th><th class="px-5 py-3.5 text-right">Purchase Price</th><th class="px-5 py-3.5 text-right">Selling Prices</th><th class="px-5 py-3.5 text-right">Total</th></tr></thead><tbody class="divide-y divide-slate-100">
            @foreach($purchase->items as $item)<tr>
                <td class="px-5 py-4 font-semibold text-slate-800">{{ $item->product->name }}</td>
                <td class="px-5 py-4"><p>{{ $item->batch?->batch_number ?: 'No batch number' }}</p><p class="mt-1 text-xs text-slate-400">{{ $item->batch?->expires_on?->format('d M Y') ?: 'No expiry' }}</p></td>
                <td class="px-5 py-4 text-right">{{ number_format($item->quantity) }} {{ Str::plural($item->purchase_unit, $item->quantity) }}</td>
                <td class="px-5 py-4 text-right font-semibold">{{ number_format($item->stock_quantity) }}</td>
                <td class="px-5 py-4 text-right">৳{{ number_format($item->purchase_price, 2) }} / {{ $item->purchase_unit }}</td>
                <td class="px-5 py-4 text-right"><p>৳{{ number_format($item->sale_price, 2) }} / piece</p>@if($item->strip_sale_price)<p class="mt-1 text-xs text-slate-500">৳{{ number_format($item->strip_sale_price, 2) }} / strip</p>@endif</td>
                <td class="px-5 py-4 text-right font-bold">৳{{ number_format($item->line_total, 2) }}</td>
            </tr>@endforeach
        </tbody></table></div>
        <div class="flex justify-end border-t border-slate-200 bg-slate-50 p-6"><dl class="w-full max-w-sm space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold">৳{{ number_format($purchase->subtotal, 2) }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd class="font-semibold">৳{{ number_format($purchase->discount, 2) }}</dd></div><div class="flex justify-between border-t border-slate-200 pt-3 text-base"><dt class="font-bold">Total</dt><dd class="font-black text-blue-700">৳{{ number_format($purchase->total, 2) }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Paid</dt><dd class="font-semibold text-emerald-700">৳{{ number_format($purchase->paid, 2) }}</dd></div><div class="flex justify-between rounded-md bg-amber-50 px-3 py-2"><dt class="font-semibold text-amber-800">Due</dt><dd class="font-bold text-amber-800">৳{{ number_format($purchase->due, 2) }}</dd></div></dl></div>
    </section>
    <p class="mt-3 text-center text-xs text-slate-400 print:hidden">Choose <strong>Save as PDF</strong> in the print dialog to download this invoice.</p>
    <script>
        function downloadPurchaseInvoice() {
            const oldTitle = document.title;
            document.title = '{{ $purchase->invoice_number }}';
            window.print();
            window.setTimeout(() => { document.title = oldTitle; }, 500);
        }
    </script>
</x-app-layout>
