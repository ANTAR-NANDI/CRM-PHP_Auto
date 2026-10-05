@php
    $initialItems = old('items', [['product_id' => '', 'batch_number' => '', 'expires_on' => '', 'purchase_unit' => 'piece', 'quantity' => 1, 'purchase_price' => '', 'single_sale_price' => '', 'strip_sale_price' => '']]);
    $productUnits = $products->mapWithKeys(fn ($product) => [$product->id => ['pieces_per_strip' => $product->pieces_per_strip, 'sell_by_piece' => $product->sell_by_piece, 'sell_by_strip' => $product->sell_by_strip]]);
@endphp
<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-bold text-slate-900">New Purchase</h1><p class="mt-1 text-sm text-slate-500">Receive medicine batches from a supplier and update stock instantly.</p></div></x-slot>

    @if($errors->any())<div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><p class="font-bold">Please correct the purchase information:</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if($suppliers->isEmpty() || $products->isEmpty())
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"><p class="font-bold">Purchase setup is incomplete.</p><p class="mt-1">You need at least one active supplier and one active medicine.</p><div class="mt-3 flex gap-2">@if($suppliers->isEmpty())<a href="{{ route('suppliers.create') }}" class="rounded-md bg-amber-600 px-3 py-2 font-semibold text-white">Add Supplier</a>@endif @if($products->isEmpty())<a href="{{ route('products.create') }}" class="rounded-md bg-amber-600 px-3 py-2 font-semibold text-white">Add Medicine</a>@endif</div></div>
    @endif

    <form method="POST" action="{{ route('purchases.store') }}" x-data="{
        items: {{ Js::from($initialItems) }},
        products: {{ Js::from($productUnits) }},
        discount: {{ Js::from((float) old('discount', 0)) }},
        paid: {{ Js::from((float) old('paid', 0)) }},
        addItem() { this.items.push({ product_id: '', batch_number: '', expires_on: '', purchase_unit: 'piece', quantity: 1, purchase_price: '', single_sale_price: '', strip_sale_price: '' }) },
        removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1) },
        productConfig(item) { return this.products[item.product_id] || { pieces_per_strip: 1, sell_by_piece: true, sell_by_strip: false } },
        stockPieces(item) { return (Number(item.quantity) || 0) * (item.purchase_unit === 'strip' ? Number(this.productConfig(item).pieces_per_strip) : 1) },
        lineTotal(item) { return (Number(item.quantity) || 0) * (Number(item.purchase_price) || 0) },
        get subtotal() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0) },
        get total() { return Math.max(0, this.subtotal - (Number(this.discount) || 0)) },
        get due() { return Math.max(0, this.total - (Number(this.paid) || 0)) },
        money(value) { return Number(value || 0).toFixed(2) }
    }">
        @csrf
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white">Supplier & Invoice</div>
            <div class="grid gap-5 p-6 md:grid-cols-2">
                <div><label class="text-sm font-semibold text-slate-700">Supplier <span class="text-rose-500">*</span></label><select name="supplier_id" required class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="">Select supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></div>
                <div><label class="text-sm font-semibold text-slate-700">Purchase date <span class="text-rose-500">*</span></label><input type="date" name="purchased_at" value="{{ old('purchased_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="mt-2 block w-full rounded-md border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"></div>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between bg-gradient-to-r from-[#4165dd] to-[#2947bd] px-5 py-3 text-sm font-semibold text-white"><span>Medicine Batches</span><button type="button" @click="addItem()" class="rounded-md bg-white/15 px-3 py-1.5 text-xs font-bold hover:bg-white/25">+ Add Row</button></div>
            <div class="overflow-x-auto"><table class="min-w-[1450px] w-full text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="w-[22%] px-4 py-3">Medicine</th><th class="px-4 py-3">Batch</th><th class="px-4 py-3">Expiry</th><th class="px-4 py-3">Receive As</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Buy / Unit</th><th class="px-4 py-3">Sell Single</th><th class="px-4 py-3">Sell Strip</th><th class="px-4 py-3 text-right">Line Total</th><th class="w-12 px-4 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">
                <template x-for="(item, index) in items" :key="index"><tr>
                    <td class="px-4 py-3"><select :name="`items[${index}][product_id]`" x-model="item.product_id" required class="block w-full rounded-md border-slate-300 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"><option value="">Select medicine</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}{{ $product->brand ? ' — '.$product->brand->name : '' }}</option>@endforeach</select></td>
                    <td class="px-4 py-3"><input :name="`items[${index}][batch_number]`" x-model="item.batch_number" placeholder="Optional" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"></td>
                    <td class="px-4 py-3"><input type="date" :name="`items[${index}][expires_on]`" x-model="item.expires_on" min="{{ now()->addDay()->toDateString() }}" class="w-full rounded-md border-slate-300 px-3 py-2 text-sm"></td>
                    <td class="px-4 py-3"><select :name="`items[${index}][purchase_unit]`" x-model="item.purchase_unit" class="w-28 rounded-md border-slate-300 py-2 text-sm"><option value="piece">Piece</option><option value="strip" :disabled="productConfig(item).pieces_per_strip < 2">Strip</option></select></td>
                    <td class="px-4 py-3"><input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required class="w-24 rounded-md border-slate-300 px-3 py-2 text-sm"><p class="mt-1 whitespace-nowrap text-[11px] text-slate-400"><span x-text="stockPieces(item)"></span> pieces to stock</p></td>
                    <td class="px-4 py-3"><input type="number" min="0" step="0.01" :name="`items[${index}][purchase_price]`" x-model.number="item.purchase_price" required class="w-32 rounded-md border-slate-300 px-3 py-2 text-sm"></td>
                    <td class="px-4 py-3"><input type="number" min="0" step="0.01" :name="`items[${index}][single_sale_price]`" x-model.number="item.single_sale_price" :required="productConfig(item).sell_by_piece" :disabled="!productConfig(item).sell_by_piece" placeholder="Per piece" class="w-32 rounded-md border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100"></td>
                    <td class="px-4 py-3"><input type="number" min="0" step="0.01" :name="`items[${index}][strip_sale_price]`" x-model.number="item.strip_sale_price" :required="productConfig(item).sell_by_strip" :disabled="!productConfig(item).sell_by_strip" placeholder="Per strip" class="w-32 rounded-md border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100"></td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">৳<span x-text="money(lineTotal(item))"></span></td>
                    <td class="px-4 py-3"><button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="grid h-8 w-8 place-items-center rounded-md bg-rose-50 font-bold text-rose-600 hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-30">×</button></td>
                </tr></template>
            </tbody></table></div>
            <div class="border-t border-slate-200 bg-slate-50 p-5"><button type="button" @click="addItem()" class="rounded-md border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">+ Add another medicine</button></div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-[1fr_420px]">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900"><p class="font-bold">Base-piece stock conversion</p><p class="mt-2 leading-6 text-blue-700">If a strip contains 10 tablets, receiving 20 strips adds 200 pieces to stock. Later, selling one strip deducts 10 pieces; selling one tablet deducts only 1. This keeps both selling methods accurate.</p></div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><div class="space-y-4"><div class="flex items-center justify-between"><span class="text-sm text-slate-500">Subtotal</span><strong>৳<span x-text="money(subtotal)"></span></strong></div><div><label class="flex items-center justify-between text-sm text-slate-500"><span>Discount</span><input type="number" name="discount" min="0" step="0.01" x-model.number="discount" class="w-36 rounded-md border-slate-300 px-3 py-2 text-right text-sm"></label></div><div class="flex items-center justify-between border-t border-slate-200 pt-4 text-lg"><span class="font-bold text-slate-800">Total</span><strong class="text-blue-700">৳<span x-text="money(total)"></span></strong></div><div><label class="flex items-center justify-between text-sm text-slate-500"><span>Paid now</span><input type="number" name="paid" min="0" step="0.01" x-model.number="paid" class="w-36 rounded-md border-slate-300 px-3 py-2 text-right text-sm"></label></div><div class="flex items-center justify-between rounded-md bg-amber-50 px-3 py-2"><span class="text-sm font-semibold text-amber-800">Supplier due</span><strong class="text-amber-800">৳<span x-text="money(due)"></span></strong></div></div></div>
        </section>

        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('purchases.index') }}" class="rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a><button @disabled($suppliers->isEmpty() || $products->isEmpty()) class="rounded-md bg-gradient-to-r from-[#4165dd] to-[#4432f2] px-6 py-2.5 text-sm font-semibold text-white shadow hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-50">Save Purchase & Receive Stock</button></div>
    </form>
</x-app-layout>
