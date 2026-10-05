<?php

namespace App\Http\Controllers;

use App\Models\GenericName;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $products = Product::query()
            ->with(['genericName'])
            ->withSum('batches as stock_quantity', 'quantity_available')
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")))
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
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Medicine added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', ['product' => $product, ...$this->references()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.index')->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->purchaseItems()->exists() || $product->batches()->exists()) {
            return back()->with('error', 'This medicine has inventory history and cannot be deleted. Mark it inactive instead.');
        }

        $product->delete();

        return back()->with('success', 'Medicine deleted successfully.');
    }

    private function references(): array
    {
        return [
            'genericNames' => GenericName::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['tablet', 'capsule', 'syrup', 'suspension', 'injection', 'drops', 'cream', 'ointment', 'gel', 'inhaler', 'suppository', 'powder', 'sachet', 'other'])],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products')->ignore($product)],
            'generic_name_id' => ['nullable', 'exists:generic_names,id'],
            'unit' => ['required', Rule::in(['piece', 'strip', 'box', 'bottle', 'tube', 'vial', 'sachet'])],
            'pieces_per_strip' => ['required', 'integer', 'min:1', 'max:1000'],
            'sell_by_piece' => ['nullable', 'boolean'],
            'sell_by_strip' => ['nullable', 'boolean'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['barcode'] = $data['barcode'] ?: null;
        $data['sell_by_piece'] = $request->boolean('sell_by_piece');
        $data['sell_by_strip'] = $request->boolean('sell_by_strip');
        $data['is_active'] = $request->boolean('is_active');

        if (! $data['sell_by_piece'] && ! $data['sell_by_strip']) {
            throw ValidationException::withMessages(['sell_by_piece' => 'Choose at least one selling option: single piece or strip.']);
        }

        if ($data['sell_by_strip'] && $data['pieces_per_strip'] < 2) {
            throw ValidationException::withMessages(['pieces_per_strip' => 'A strip must contain at least 2 pieces.']);
        }

        return $data;
    }
}
