<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div><h1 class="text-xl font-bold text-slate-900">Medicines & Stock</h1><p class="mt-1 text-sm text-slate-500">Manage the medicine catalog and see live base-piece quantities.</p></div>
            @if(auth()->user()->hasRole('admin'))<a href="{{ route('products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-gradient-to-r from-[#4165dd] to-[#4432f2] px-4 py-2.5 text-sm font-semibold text-white shadow">+ Add Medicine</a>@endif
        </div>
    </x-slot>
    <form class="mb-5 flex max-w-xl gap-2"><input name="search" value="{{ $search }}" placeholder="Search medicine or barcode" class="block w-full rounded-md border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"><button class="rounded-md bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white">Search</button>@if($search)<a href="{{ route('products.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600">Clear</a>@endif</form>
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Medicine List</div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3.5">Medicine</th><th class="px-5 py-3.5">Generic / Brand</th><th class="px-5 py-3.5">Units</th><th class="px-5 py-3.5 text-center">Stock</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($products as $product)<tr class="hover:bg-slate-50">
                <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-400">{{ $product->barcode ?: 'No barcode' }}</p></td>
                <td class="px-5 py-4"><p class="text-slate-700">{{ $product->genericName?->name ?: '—' }}</p><p class="mt-1 text-xs text-slate-400">{{ $product->brand?->name ?: 'No brand' }}</p></td>
                <td class="px-5 py-4 text-slate-600"><p>{{ $product->pieces_per_strip }} pieces / strip</p><p class="mt-1 text-xs capitalize text-slate-400">Sell: {{ collect([$product->sell_by_piece ? 'single' : null, $product->sell_by_strip ? 'strip' : null])->filter()->join(' + ') }}</p></td>
                <td class="px-5 py-4 text-center"><span class="rounded-md px-2.5 py-1 font-bold {{ ($product->stock_quantity ?? 0) <= $product->reorder_level ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ number_format($product->stock_quantity ?? 0) }} pieces</span></td>
                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="px-5 py-4">@if(auth()->user()->hasRole('admin'))<x-table-actions :edit-url="route('products.edit', $product)" :delete-url="route('products.destroy', $product)" :item-name="$product->name" />@else<span class="text-xs font-semibold text-slate-400">View only</span>@endif</td>
            </tr>@empty<tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">No medicines found. Add your first medicine to begin purchasing.</td></tr>@endforelse
        </tbody></table></div><div class="border-t border-slate-200 px-5 py-4">{{ $products->links() }}</div>
    </section>
</x-app-layout>
