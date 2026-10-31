<x-app-layout>
    @php($columnCount = 3 + $activityHeaders->count() + $leadHeaders->count() + $salesHeaders->count())
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">Performance Report</h1><p class="mt-1 text-sm text-slate-500">Employee performance by activity, lead stage, and sales points.</p></div></x-slot>
    <form class="mb-5 flex flex-wrap items-end justify-end gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        @if($canViewAll)
            <label class="block text-xs font-semibold text-slate-600">User<select name="user" class="mt-1 block min-w-40 rounded-md border-slate-300 py-2 text-sm"><option value="">Select user</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user'] ?? null) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
        @else
            <input type="hidden" name="user" value="{{ $filters['user'] }}">
            <span class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">My performance</span>
        @endif
        <label class="block text-xs font-semibold text-slate-600">From<input name="from" type="date" value="{{ $filters['from'] }}" class="mt-1 block rounded-md border-slate-300 py-2 text-sm"></label>
        <label class="block text-xs font-semibold text-slate-600">To<input name="to" type="date" value="{{ $filters['to'] }}" class="mt-1 block rounded-md border-slate-300 py-2 text-sm"></label>
        <button class="rounded-md bg-[#1f7d88] px-5 py-2 text-sm font-semibold text-white hover:bg-[#176973]">Search</button>
    </form>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b bg-slate-50 px-4 py-3 text-sm text-slate-600">Total Score = activity count × configured activity point + lead-stage count × configured lead point + vehicle sales × {{ $salePoint }}. Period: <strong>{{ CarbonCarbon::parse($filters['from'])->format('d M Y') }}</strong> to <strong>{{ CarbonCarbon::parse($filters['to'])->format('d M Y') }}</strong>.</div>
        <div class="overflow-x-auto"><table class="min-w-[1100px] w-full border-collapse text-left text-sm"><thead class="bg-slate-50"><tr class="border-b border-slate-200 text-xs font-bold uppercase tracking-wide text-slate-600"><th rowspan="2" class="border-r border-slate-200 px-3 py-3">SL.</th><th rowspan="2" class="border-r border-slate-200 px-3 py-3">Name</th>
            @if($activityHeaders->isNotEmpty())
                <th colspan="{{ $activityHeaders->count() }}" class="border-r border-slate-200 px-3 py-3 text-center">Activities</th>
            @endif
            @if($leadHeaders->isNotEmpty())
                <th colspan="{{ $leadHeaders->count() }}" class="border-r border-slate-200 px-3 py-3 text-center">Leads</th>
            @endif
            @if($salesHeaders->isNotEmpty())
                <th colspan="{{ $salesHeaders->count() }}" class="border-r border-slate-200 px-3 py-3 text-center">Sales</th>
            @endif
            <th rowspan="2" class="px-3 py-3 text-center">Total Score</th></tr><tr class="border-b border-slate-200 text-xs font-semibold text-slate-700">
            @foreach($activityHeaders as $header)<th class="border-r border-slate-200 px-3 py-3"><span class="block">{{ $header->name }}</span><span class="text-[10px] text-teal-700">{{ $header->point }} pt</span></th>@endforeach
            @foreach($leadHeaders as $header)<th class="border-r border-slate-200 px-3 py-3"><span class="block">{{ $header->name }}</span><span class="text-[10px] text-teal-700">{{ $header->point }} pt</span></th>@endforeach
            @foreach($salesHeaders as $header)<th class="border-r border-slate-200 px-3 py-3"><span class="block">{{ $header }}</span><span class="text-[10px] text-teal-700">{{ $salePoint }} pt</span></th>@endforeach
        </tr></thead><tbody class="divide-y divide-slate-200">@forelse($rows as $row)<tr class="hover:bg-slate-50"><td class="border-r border-slate-200 px-3 py-4">{{ $loop->iteration }}</td><td class="border-r border-slate-200 px-3 py-4 font-semibold">{{ $row['name'] }}</td>
            @foreach($activityHeaders as $header)@php($count = $row['activities']->get($header->id, 0))<td class="border-r border-slate-200 px-3 py-4 text-center">{{ $count ?: '' }}</td>@endforeach
            @foreach($leadHeaders as $header)@php($count = $row['leads']->get($header->id, 0))<td class="border-r border-slate-200 px-3 py-4 text-center">{{ $count ?: '' }}</td>@endforeach
            @foreach($salesHeaders as $header)@php($count = $row['sales']->get($header, 0))<td class="border-r border-slate-200 px-3 py-4 text-center">{{ $count ?: '' }}</td>@endforeach
            <td class="px-3 py-4 text-center font-bold text-[#1f7d88]">{{ number_format($row['total_score']) }}</td></tr>@empty<tr><td colspan="{{ $columnCount }}" class="px-5 py-14 text-center text-slate-500">No employee performance data found for the selected period.</td></tr>@endforelse</tbody></table></div>
        <div class="border-t bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">Combined Total Score: <span class="text-[#1f7d88]">{{ number_format(collect($rows)->sum('total_score')) }}</span></div>
    </section>
    <details class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><summary class="cursor-pointer font-semibold text-slate-800">View point system</summary><div class="mt-4 grid gap-5 md:grid-cols-3"><div><h2 class="mb-2 text-sm font-bold text-slate-700">Activity Points</h2><dl class="divide-y text-sm">@forelse($activityHeaders as $header)<div class="flex justify-between py-2"><dt>{{ $header->name }}</dt><dd class="font-semibold">{{ $header->point }}</dd></div>@empty<p class="py-2 text-slate-500">No activity types.</p>@endforelse</dl></div><div><h2 class="mb-2 text-sm font-bold text-slate-700">Lead Points</h2><dl class="divide-y text-sm">@forelse($leadHeaders as $header)<div class="flex justify-between py-2"><dt>{{ $header->name }}</dt><dd class="font-semibold">{{ $header->point }}</dd></div>@empty<p class="py-2 text-slate-500">No lead statuses.</p>@endforelse</dl></div><div><h2 class="mb-2 text-sm font-bold text-slate-700">Sales Points</h2><p class="py-2 text-sm">Each vehicle sale quantity earns <strong>{{ $salePoint }} points</strong>.</p></div></div></details>
</x-app-layout>
