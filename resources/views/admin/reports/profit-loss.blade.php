<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.16em] text-[#315df4]">Financial Reports</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">Profit &amp; Loss Report</h1>
                <p class="mt-1 text-sm text-slate-500">Income and expense from posted accounting vouchers.</p>
            </div>
            <button onclick="window.print()" class="rounded-lg bg-[#2d63e9] px-5 py-2.5 text-sm font-bold text-white print:hidden">Print</button>
        </div>
    </x-slot>

    <form class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3 print:hidden">
        <div><label class="text-xs font-bold uppercase text-slate-500">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div><label class="text-xs font-bold uppercase text-slate-500">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div class="flex items-end"><button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-bold text-white">Apply Filter</button></div>
    </form>

    <section class="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-7 shadow-sm print:border-0 print:shadow-none">
        <div class="border-b border-slate-200 pb-5 text-center"><h2 class="text-xl font-black">MediStock Pharmacy</h2><p class="mt-1 font-bold">Profit &amp; Loss Statement</p><p class="mt-1 text-sm text-slate-500">{{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p></div>
        <div class="mt-6"><h3 class="border-b border-slate-200 pb-2 font-black text-emerald-700">Income</h3>@forelse($income as $row)<div class="flex justify-between py-3 text-sm"><span>{{ $row->code }} — {{ $row->name }}</span><span class="font-semibold">৳{{ number_format($row->amount, 2) }}</span></div>@empty<div class="py-3 text-sm text-slate-500">No income vouchers in this period.</div>@endforelse<div class="flex justify-between border-t border-slate-300 py-3 font-black"><span>Total Income</span><span>৳{{ number_format($totalIncome, 2) }}</span></div></div>
        <div class="mt-7"><h3 class="border-b border-slate-200 pb-2 font-black text-rose-700">Expenses</h3>@forelse($expenses as $row)<div class="flex justify-between py-3 text-sm"><span>{{ $row->code }} — {{ $row->name }}</span><span class="font-semibold">৳{{ number_format($row->amount, 2) }}</span></div>@empty<div class="py-3 text-sm text-slate-500">No expense vouchers in this period.</div>@endforelse<div class="flex justify-between border-t border-slate-300 py-3 font-black"><span>Total Expenses</span><span>৳{{ number_format($totalExpenses, 2) }}</span></div></div>
        <div class="mt-7 flex justify-between rounded-lg {{ $totalIncome - $totalExpenses >= 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }} px-5 py-4 text-lg font-black"><span>Net {{ $totalIncome - $totalExpenses >= 0 ? 'Profit' : 'Loss' }}</span><span>৳{{ number_format(abs($totalIncome - $totalExpenses), 2) }}</span></div>
    </section>
</x-app-layout>
