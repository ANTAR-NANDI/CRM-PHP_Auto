<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.16em] text-[#315df4]">Financial Reports</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900">General Ledger</h1>
                <p class="mt-1 text-sm text-slate-500">Head-wise transaction statement from posted account vouchers.</p>
            </div>
            @if($account)<button onclick="window.print()" class="rounded-lg bg-[#2d63e9] px-5 py-3 text-sm font-bold text-white print:hidden">Print Ledger</button>@endif
        </div>
    </x-slot>

    <form class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 print:hidden">
        <div class="md:col-span-2">
            <label class="text-xs font-bold uppercase text-slate-500">Account Head</label>
            <select name="account_id" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm" required>
                <option value="">Select account head</option>
                @foreach($accounts as $item)<option value="{{ $item->id }}" @selected($account?->id === $item->id)>{{ $item->code }} — {{ $item->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-xs font-bold uppercase text-slate-500">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div><label class="text-xs font-bold uppercase text-slate-500">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm"></div>
        <div class="md:col-span-4"><button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-bold text-white">View Ledger</button></div>
    </form>

    @if(! $account)
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm"><p class="text-lg font-bold text-slate-700">Select an account head to view its ledger.</p><p class="mt-2 text-sm text-slate-500">Only active transactional heads appear in this report.</p></div>
    @else
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
            <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-6 py-6 text-white print:bg-white print:text-slate-900">
                <p class="text-xl font-black">{{ $account->code }} — {{ $account->name }}</p>
                <p class="mt-1 text-sm">GENERAL LEDGER • {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-white text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Date</th><th class="px-5 py-3.5">Voucher</th><th class="px-5 py-3.5">Narration</th><th class="px-5 py-3.5 text-right">Debit</th><th class="px-5 py-3.5 text-right">Credit</th><th class="px-5 py-3.5 text-right">Balance</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-slate-50"><td colspan="5" class="px-5 py-4 font-bold text-slate-700">Opening Balance</td><td class="px-5 py-4 text-right font-black">{{ $opening >= 0 ? 'Dr ' : 'Cr ' }}৳{{ number_format(abs($opening), 2) }}</td></tr>
                        @forelse($rows as $row)
                            <tr class="hover:bg-slate-50"><td class="px-5 py-4">{{ $row->voucher_date->format('d M Y') }}</td><td class="px-5 py-4 font-mono font-bold text-[#203D9F]">{{ $row->voucher_number }}</td><td class="px-5 py-4 text-slate-600">{{ $row->narration ?: $row->note ?: '—' }}</td><td class="px-5 py-4 text-right">{{ $row->debit > 0 ? '৳'.number_format($row->debit, 2) : '—' }}</td><td class="px-5 py-4 text-right">{{ $row->credit > 0 ? '৳'.number_format($row->credit, 2) : '—' }}</td><td class="px-5 py-4 text-right font-bold">{{ $row->running_balance >= 0 ? 'Dr ' : 'Cr ' }}৳{{ number_format(abs($row->running_balance), 2) }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No posted transactions in the selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
