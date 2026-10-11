<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h1 class="text-xl font-bold text-slate-900">Delivery Challan List</h1><p class="mt-1 text-sm text-slate-500">Signed challans linked to customer sales.</p></div>
            <a href="{{ route('sales-documents.create') }}" class="rounded bg-[#1f7d88] px-4 py-2 text-sm font-semibold text-white">+ Add Delivery Challan</a>
        </div>
    </x-slot>

    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form class="mb-4 flex justify-end gap-2"><input name="search" value="{{ $search }}" placeholder="Challan title or sale no." class="rounded border-slate-300 text-sm"><button class="rounded bg-[#1f7d88] px-4 text-sm font-semibold text-white">Search</button></form>
        <div class="overflow-x-auto"><table class="js-data-table w-full min-w-[900px] text-left text-sm"><thead><tr><th>SL.</th><th>Challan</th><th>Sale</th><th>Customer</th><th>Upload Date</th><th>Note</th><th>Actions</th></tr></thead><tbody>@forelse($documents as $document)<tr><td>{{ $loop->iteration }}</td><td class="font-semibold">{{ $document->title }}</td><td>{{ $document->sale_no ?: '—' }}</td><td>{{ $document->customer_name ?: '—' }}</td><td>{{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y h:i A') }}</td><td>{{ $document->remarks ?: '—' }}</td><td class="whitespace-nowrap">@if($document->customer_vehicle_sale_id)<a href="{{ route('sales-documents.export', $document->id) }}" target="_blank" class="font-semibold text-teal-700 hover:underline">Export PDF</a>@endif <a href="{{ route('sales-documents.download', $document->id) }}" class="ml-3 font-semibold text-slate-700 hover:underline">Download File</a><form method="POST" action="{{ route('sales-documents.destroy', $document->id) }}" class="ml-3 inline" onsubmit="return confirm('Delete this document?')">@csrf @method('DELETE')<button class="font-semibold text-rose-600 hover:underline">Delete</button></form></td></tr>@empty<tr><td colspan="7" class="p-10 text-center text-slate-500">No delivery challans found.</td></tr>@endforelse</tbody></table></div>
    </section>
</x-app-layout>
