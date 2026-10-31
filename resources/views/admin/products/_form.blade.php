@csrf
<div x-data="{ rows: [{store:'', position:'', qty:0}], add(){ this.rows.push({store:'', position:'', qty:0}) }, remove(i){ if(this.rows.length > 1) this.rows.splice(i, 1) } }">
    <div class="border-b border-slate-200 pb-3">
        <h2 class="text-lg font-medium text-slate-800">General Info</h2>
        <p class="mt-1 text-sm text-slate-500">Enter the product details used by sales and inventory.</p>
    </div>

    <div class="mt-6 grid gap-x-6 gap-y-5 md:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-700">Segment
            <select name="segment_id" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Select segment</option>
                @foreach($segments as $segment)<option value="{{ $segment->id }}" @selected(old('segment_id', $product->segment_id ?? '') == $segment->id)>{{ $segment->name }}</option>@endforeach
            </select>
        </label>
        <label class="block text-sm font-semibold text-slate-700">Product Name <span class="text-rose-500">*</span>
            <input name="name" required value="{{ old('name', $product->name ?? '') }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
        </label>
        <label class="block text-sm font-semibold text-slate-700">Description
            <textarea name="description" rows="7" class="mt-2 block w-full resize-y rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('description', $product->description ?? '') }}</textarea>
        </label>
        <label class="block text-sm font-semibold text-slate-700">Technical Specification
            <textarea name="technical_specification" rows="7" class="mt-2 block w-full resize-y rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('technical_specification', $product->technical_specification ?? '') }}</textarea>
        </label>
        <label class="block text-sm font-semibold text-slate-700">Unit Price <span class="text-rose-500">*</span>
            <input name="unit_price" type="number" min="0" step=".01" required value="{{ old('unit_price', $product->unit_price ?? 0) }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
        </label>
        <label class="block text-sm font-semibold text-slate-700">Commission Type
            <select name="commission_type" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="percent" @selected(old('commission_type', $product->commission_type ?? 'percent') === 'percent')>Percent</option>
                <option value="fixed" @selected(old('commission_type', $product->commission_type ?? 'percent') === 'fixed')>Fixed amount</option>
            </select>
        </label>
        <label class="block text-sm font-semibold text-slate-700">Unit Commission
            <input name="unit_commission" type="number" min="0" step=".01" value="{{ old('unit_commission', $product->unit_commission ?? 0) }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
        </label>
        <label class="mt-7 flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> Active product</label>
    </div>

    <details class="mt-7 rounded-lg border border-slate-200 bg-slate-50" {{ $errors->hasAny(['part_no', 'item_category_id', 'unit', 'reorder_level']) ? 'open' : '' }}>
        <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-700">Inventory details (optional)</summary>
        <div class="grid gap-x-6 gap-y-5 border-t border-slate-200 p-5 md:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700">Part No<input name="part_no" value="{{ old('part_no', $product->part_no ?? '') }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>
            <label class="block text-sm font-semibold text-slate-700">Category<select name="item_category_id" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('item_category_id', $product->item_category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label class="block text-sm font-semibold text-slate-700">Unit<select name="unit" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"><option value="piece" @selected(old('unit', $product->unit ?? 'piece') === 'piece')>Piece</option><option value="box" @selected(old('unit', $product->unit ?? '') === 'box')>Box</option><option value="set" @selected(old('unit', $product->unit ?? '') === 'set')>Set</option><option value="kg" @selected(old('unit', $product->unit ?? '') === 'kg')>Kg</option><option value="liter" @selected(old('unit', $product->unit ?? '') === 'liter')>Liter</option></select></label>
            <label class="block text-sm font-semibold text-slate-700">Reorder Level<input name="reorder_level" type="number" min="0" step=".01" value="{{ old('reorder_level', $product->reorder_level ?? 0) }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>
            @unless(isset($product))<label class="block text-sm font-semibold text-slate-700">Opening Date<input name="opening_date" type="date" value="{{ old('opening_date', now()->format('Y-m-d')) }}" class="mt-2 block w-full rounded-md border-slate-300 py-2.5 text-sm"></label>@endunless
        </div>
        @unless(isset($product))
            <div class="border-t border-slate-200 p-5"><p class="mb-3 text-sm font-semibold text-slate-700">Opening Stock</p><div class="overflow-x-auto"><table class="min-w-[650px] w-full text-sm"><thead class="bg-white text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Store</th><th class="p-3">Store Position</th><th class="p-3">Quantity</th><th></th></tr></thead><tbody><template x-for="(row, i) in rows" :key="i"><tr class="border-t border-slate-200"><td class="p-3"><select x-model="row.store" :name="`opening_stocks[${i}][store_id]`" class="w-full rounded border-slate-300"><option value="">Select store</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach</select></td><td class="p-3"><select :name="`opening_stocks[${i}][store_position_id]`" class="w-full rounded border-slate-300"><option value="">Select position</option>@foreach($positions as $position)<option value="{{ $position->id }}">{{ $position->name }}</option>@endforeach</select></td><td class="p-3"><input x-model.number="row.qty" :name="`opening_stocks[${i}][quantity]`" type="number" min="0" step=".01" class="w-full rounded border-slate-300"></td><td class="p-3"><button type="button" @click="remove(i)" class="font-semibold text-rose-600">Remove</button></td></tr></template></tbody></table></div><button type="button" @click="add" class="mt-3 rounded border border-teal-200 bg-teal-50 px-3 py-2 text-sm font-semibold text-teal-800">+ Add stock row</button></div>
        @endunless
    </details>

    <div class="mt-7 flex gap-3"><button class="rounded-md bg-[#1f7d88] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#176973]">{{ isset($product) ? 'Update Product' : 'Save' }}</button><a href="{{ route('products.index') }}" class="crm-cancel-button rounded-md px-5 py-2.5 text-sm">Cancel</a></div>
</div>
