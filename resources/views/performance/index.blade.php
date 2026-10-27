<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Performance Report</h1>
            <p class="mt-1 text-sm text-slate-500">Live activity, lead, and vehicle-sale performance from your CRM records.</p>
        </div>
    </x-slot>

    <form class="mb-5 flex flex-wrap justify-end gap-2 rounded-lg border border-slate-200 bg-white p-4">
        @if($canViewAll)<select name="user" class="rounded-md border-slate-300 py-2 text-sm"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user'] ?? null) == $user->id)>{{ $user->name }}</option>@endforeach</select>@else<input type="hidden" name="user" value="{{ $filters['user'] }}"><span class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">My performance</span>@endif
        <input name="from" type="date" value="{{ $filters['from'] }}" class="rounded-md border-slate-300 py-2 text-sm">
        <input name="to" type="date" value="{{ $filters['to'] }}" class="rounded-md border-slate-300 py-2 text-sm">
        <button class="rounded-md bg-[#1f7d88] px-5 text-sm font-semibold text-white">Search</button>
    </form>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b bg-slate-50 px-4 py-3 text-sm text-slate-600">Report period: <strong>{{ \Carbon\Carbon::parse($filters['from'])->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($filters['to'])->format('d M Y') }}</strong></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50"><tr class="text-xs font-bold uppercase text-slate-500"><th rowspan="2" class="border-r px-3 py-3">SL</th><th rowspan="2" class="border-r px-3 py-3">Name</th>@if($activityHeaders->isNotEmpty())<th colspan="{{ $activityHeaders->count() }}" class="border-r px-3 py-3 text-center">Activities</th>@endif @if($leadHeaders->isNotEmpty())<th colspan="{{ $leadHeaders->count() }}" class="border-r px-3 py-3 text-center">Leads</th>@endif @if($salesHeaders->isNotEmpty())<th colspan="{{ $salesHeaders->count() }}" class="px-3 py-3 text-center">Sales Qty</th>@endif</tr><tr class="text-xs font-bold text-slate-700">@foreach($activityHeaders as $header)<th class="border-r px-3 py-3">{{ $header }}</th>@endforeach @foreach($leadHeaders as $header)<th class="border-r px-3 py-3">{{ $header }}</th>@endforeach @foreach($salesHeaders as $header)<th class="border-r px-3 py-3">{{ $header }}</th>@endforeach</tr></thead><tbody class="divide-y divide-slate-100">@forelse($rows as $row)<tr><td class="border-r px-3 py-4">{{ $loop->iteration }}</td><td class="border-r px-3 py-4 font-semibold">{{ $row['name'] }}</td>@foreach($activityHeaders as $id => $header)<td class="border-r px-3 py-4 text-center">{{ $row['activities']->get($id, 0) }}</td>@endforeach @foreach($leadHeaders as $id => $header)<td class="border-r px-3 py-4 text-center">{{ $row['leads']->get($id, 0) }}</td>@endforeach @foreach($salesHeaders as $header)<td class="border-r px-3 py-4 text-center">{{ $row['sales']->get($header, 0) }}</td>@endforeach</tr>@empty<tr><td colspan="2" class="px-5 py-14 text-center text-slate-500">No user found for the selected filter.</td></tr>@endforelse</tbody></table></div>
        @if($activityHeaders->isEmpty() && $leadHeaders->isEmpty() && $salesHeaders->isEmpty())<p class="border-t px-4 py-3 text-sm text-amber-700">Add Activity Types, Lead Statuses, and Item Categories first to see report columns.</p>@endif
    </section>
</x-app-layout>
