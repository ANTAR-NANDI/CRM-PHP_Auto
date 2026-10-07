<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div><h1 class="text-xl font-bold text-slate-900">Item List</h1><p class="mt-1 text-sm text-slate-500">Manage item codes, categories, landing cost, and stock levels.</p></div>
            @if(auth()->user()->hasRole('admin'))<a href="{{ route('products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-[#1f7d88] px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-[#176973]">+ Add Item</a>@endif
        </div>
    </x-slot>
    <form class="mb-5 flex max-w-xl gap-2"><input name="search" value="{{ $search }}" placeholder="Search item name or part no" class="block w-full rounded-md border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500"><button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white">Search</button>@if($search)<a href="{{ route('products.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Clear</a>@endif</form>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-[#1f7d88] px-5 py-3 text-sm font-semibold text-white">Item List</div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Part No / Item</th><th class="px-5 py-3.5">Category</th><th class="px-5 py-3.5">Unit</th><th class="px-5 py-3.5 text-right">Landing Cost</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($products as $product)<tr class="hover:bg-slate-50">
                <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $product->part_no ?: 'No part no' }}</p></td>
                <td class="px-5 py-4 text-slate-600">{{ $product->itemCategory?->name ?: '—' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ ucfirst($product->unit) }}</td>
                <td class="px-5 py-4 text-right font-semibold text-slate-800">৳{{ number_format($product->unit_price ?? 0, 2) }}</td>
                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="px-5 py-4">@if(auth()->user()->hasRole('admin'))<x-table-actions :edit-url="route('products.edit', $product)" :delete-url="route('products.destroy', $product)" :item-name="$product->name" />@else<span class="text-xs font-semibold text-slate-400">View only</span>@endif</td>
            </tr>@empty<tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">No items found. Add your first item to begin.</td></tr>@endforelse
        </tbody></table></div><div class="border-t border-slate-200 px-5 py-4">{{ $products->links() }}</div>
    </section>
</x-app-layout>
