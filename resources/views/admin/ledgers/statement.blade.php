<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.16em] text-[#315df4]">Accounts</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">{{ $type === 'customer' ? 'Customer Ledger' : 'Supplier Ledger' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Individual statement with opening balance, invoices, payments, and running due.</p>
            </div>
            @if($party)<button onclick="window.print()" class="rounded-lg bg-[#2d63e9] px-5 py-3 text-sm font-bold text-white print:hidden">Print Statement</button>@endif
        </div>
    </x-slot>

    <form class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 print:hidden">
        <div class="md:col-span-2"><label class="text-xs font-bold uppercase text-slate-500">{{ $type === 'customer' ? 'Customer' : 'Supplier' }}</label><select name="{{ $type }}_id" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"><option value="">Select {{ $type === 'customer' ? 'a customer' : 'a supplier' }}</option>@foreach($parties as $item)<option value="{{ $item->id }}" @selected($party?->id === $item->id)>{{ $item->name }}{{ $type === 'customer' && $item->phone ? ' — '.$item->phone : '' }}</option>@endforeach</select></div>
        <div><label class="text-xs font-bold uppercase text-slate-500">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div><label class="text-xs font-bold uppercase text-slate-500">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div class="md:col-span-4"><button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-bold text-white">View Ledger</button></div>
    </form>

    @if(! $party)
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm"><p class="text-lg font-bold text-slate-700">Select a {{ $type }} to view the ledger.</p><p class="mt-2 text-sm text-slate-500">Choose a date range, then click “View Ledger”.</p></div>
    @else
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
            <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-6 py-6 text-white print:bg-white print:text-slate-900">
                <div class="flex flex-col justify-between gap-3 sm:flex-row"><div><p class="text-xl font-black">MediStock Pharmacy</p><p class="mt-1 text-sm">{{ strtoupper($type) }} LEDGER STATEMENT</p></div><div class="sm:text-right"><p class="font-bold">{{ $party->name }}</p><p class="mt-1 text-sm">{{ $party->phone ?: 'No phone recorded' }}</p><p class="mt-1 text-sm">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p></div></div>
            </div>
            <div class="grid border-b border-slate-200 bg-slate-50 text-center sm:grid-cols-3"><div class="p-4"><p class="text-xs font-bold uppercase text-slate-400">Opening Balance</p><p class="mt-1 text-lg font-black text-slate-800">৳{{ number_format($opening, 2) }}</p></div><div class="border-y border-slate-200 p-4 sm:border-x sm:border-y-0"><p class="text-xs font-bold uppercase text-slate-400">Period Movement</p><p class="mt-1 text-lg font-black text-[#203D9F]">{{ $rows->count() }} entries</p></div><div class="p-4"><p class="text-xs font-bold uppercase text-slate-400">Closing {{ $type === 'customer' ? 'Due' : 'Payable' }}</p><p class="mt-1 text-lg font-black {{ $closing > 0 ? 'text-rose-600' : 'text-emerald-600' }}">৳{{ number_format($closing, 2) }}</p></div></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-slate-200 bg-white text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Date</th><th class="px-5 py-3.5">Reference</th><th class="px-5 py-3.5">Description</th><th class="px-5 py-3.5 text-right">Debit</th><th class="px-5 py-3.5 text-right">Credit</th><th class="px-5 py-3.5 text-right">Balance</th></tr></thead><tbody class="divide-y divide-slate-100"><tr class="bg-slate-50"><td colspan="5" class="px-5 py-4 font-bold text-slate-700">Opening Balance</td><td class="px-5 py-4 text-right font-black">৳{{ number_format($opening, 2) }}</td></tr>@forelse($rows as $row)<tr class="hover:bg-slate-50"><td class="px-5 py-4">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y') }}</td><td class="px-5 py-4"><a href="{{ $row['url'] }}" class="font-mono font-bold text-[#203D9F] hover:underline">{{ $row['reference'] }}</a></td><td class="px-5 py-4 text-slate-600">{{ $row['description'] }}</td><td class="px-5 py-4 text-right">{{ $row['debit'] > 0 ? '৳'.number_format($row['debit'], 2) : '—' }}</td><td class="px-5 py-4 text-right">{{ $row['credit'] > 0 ? '৳'.number_format($row['credit'], 2) : '—' }}</td><td class="px-5 py-4 text-right font-bold">৳{{ number_format($row['balance'], 2) }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No transactions in the selected period.</td></tr>@endforelse</tbody><tfoot class="border-t-2 border-slate-700 bg-slate-50"><tr><th colspan="5" class="px-5 py-4 text-right">Closing {{ $type === 'customer' ? 'Due' : 'Payable' }}</th><th class="px-5 py-4 text-right text-base">৳{{ number_format($closing, 2) }}</th></tr></tfoot></table></div>
        </section>
    @endif
</x-app-layout>
