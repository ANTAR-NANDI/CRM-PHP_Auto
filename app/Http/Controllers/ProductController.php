<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ItemCategory;
use App\Models\ProductOpeningStock;
use App\Models\Store;
use App\Models\StorePosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $products = Product::query()
            ->with(['itemCategory'])->withSum('batches as stock_quantity', 'quantity_available')
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")->orWhere('part_no', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('admin.products.create', $this->references());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data): void {
            $product = Product::create(collect($data)->except(['opening_stocks', 'opening_date'])->all());
            foreach ($data['opening_stocks'] ?? [] as $stock) {
                if (!empty($stock['store_id']) && (float) $stock['quantity'] > 0) ProductOpeningStock::create($stock + ['product_id' => $product->id, 'opening_date' => $data['opening_date']]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Item added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', ['product' => $product, ...$this->references()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update(collect($this->validated($request, $product))->except(['opening_stocks', 'opening_date'])->all());

        return redirect()->route('products.index')->with('success', 'Vehicle model updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->purchaseItems()->exists() || $product->batches()->exists()) {
            return back()->with('error', 'This vehicle model has inventory history and cannot be deleted. Mark it inactive instead.');
        }

        $product->delete();

        return back()->with('success', 'Vehicle model deleted successfully.');
    }

    private function references(): array
    {
        return [
            'categories' => ItemCategory::orderBy('name')->get(),
            'stores' => Store::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => StorePosition::where('is_active', true)->orderBy('name')->get(['id', 'store_id', 'name']),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'part_no' => ['nullable', 'string', 'max:255', Rule::unique('products', 'part_no')->ignore($product)],
            'item_category_id' => ['nullable', 'exists:item_categories,id'], 'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'], 'reorder_level' => ['nullable', 'numeric', 'min:0'], 'opening_date' => ['nullable', 'date'],
            'opening_stocks' => ['nullable', 'array'], 'opening_stocks.*.store_id' => ['nullable', 'exists:stores,id'],
            'opening_stocks.*.store_position_id' => ['nullable', 'exists:store_positions,id'], 'opening_stocks.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data += ['category' => 'item', 'commission_type' => 'fixed', 'unit_commission' => 0, 'pieces_per_strip' => 1, 'sell_by_piece' => true, 'sell_by_strip' => false, 'reorder_level' => 0, 'opening_date' => now()->toDateString()];

        return $data;
    }
}
