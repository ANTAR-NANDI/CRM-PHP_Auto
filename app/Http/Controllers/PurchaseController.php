<?php

namespace App\Http\Controllers;

use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\AccountingPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->withCount('items')
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"))))
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.purchases.index', compact('purchases', 'search'));
    }

    public function create(): View
    {
        return view('admin.purchases.create', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->with(['brand', 'genericName'])->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['required', 'date', 'before_or_equal:today'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.expires_on' => ['nullable', 'date', 'after:today'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.purchase_unit' => ['required', Rule::in(['piece', 'strip'])],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.single_sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.strip_sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $products = Product::query()->whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $unitErrors = [];

        foreach ($data['items'] as $index => &$row) {
            $product = $products->get($row['product_id']);
            $piecesPerStrip = max(1, (int) $product->pieces_per_strip);
            $multiplier = $row['purchase_unit'] === 'strip' ? $piecesPerStrip : 1;
            $unitCost = round((float) $row['purchase_price'] / $multiplier, 4);

            if ($row['purchase_unit'] === 'strip' && $piecesPerStrip < 2) {
                $unitErrors["items.{$index}.purchase_unit"] = "{$product->name} is not configured with pieces per strip.";
            }
            if ($product->sell_by_piece && ! isset($row['single_sale_price'])) {
                $unitErrors["items.{$index}.single_sale_price"] = 'Enter the single-piece selling price.';
            }
            if ($product->sell_by_strip && ! isset($row['strip_sale_price'])) {
                $unitErrors["items.{$index}.strip_sale_price"] = 'Enter the full-strip selling price.';
            }
            if (isset($row['single_sale_price']) && (float) $row['single_sale_price'] < $unitCost) {
                $unitErrors["items.{$index}.single_sale_price"] = 'Single selling price cannot be lower than its purchase cost.';
            }
            if (isset($row['strip_sale_price']) && (float) $row['strip_sale_price'] < ($unitCost * $piecesPerStrip)) {
                $unitErrors["items.{$index}.strip_sale_price"] = 'Strip selling price cannot be lower than its purchase cost.';
            }

            $row['units_per_purchase_unit'] = $multiplier;
            $row['stock_quantity'] = (int) $row['quantity'] * $multiplier;
            $row['unit_cost'] = $unitCost;
            $row['base_sale_price'] = isset($row['single_sale_price'])
                ? (float) $row['single_sale_price']
                : round((float) $row['strip_sale_price'] / $piecesPerStrip, 2);
        }
        unset($row);

        if ($unitErrors) {
            throw ValidationException::withMessages($unitErrors);
        }

        $subtotal = collect($data['items'])->sum(fn ($item) => round($item['quantity'] * $item['purchase_price'], 2));
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $total = max(0, round($subtotal - $discount, 2));
        $paid = round((float) ($data['paid'] ?? 0), 2);

        if ($discount > $subtotal) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot be greater than the subtotal.']);
        }

        if ($paid > $total) {
            return back()->withInput()->withErrors(['paid' => 'Paid amount cannot be greater than the total.']);
        }

        $purchase = DB::transaction(function () use ($data, $subtotal, $discount, $total, $paid, $request) {
            $purchase = Purchase::create([
                'invoice_number' => $this->invoiceNumber(),
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'purchased_at' => $data['purchased_at'],
            ]);

            foreach ($data['items'] as $row) {
                $batch = MedicineBatch::create([
                    'product_id' => $row['product_id'],
                    'supplier_id' => $data['supplier_id'],
                    'batch_number' => $row['batch_number'] ?? null,
                    'expires_on' => $row['expires_on'] ?? null,
                    'quantity_received' => $row['stock_quantity'],
                    'quantity_available' => $row['stock_quantity'],
                    'purchase_price' => $row['unit_cost'],
                    'sale_price' => $row['base_sale_price'],
                    'strip_sale_price' => $row['strip_sale_price'] ?? null,
                ]);

                $item = $purchase->items()->create([
                    'product_id' => $row['product_id'],
                    'medicine_batch_id' => $batch->id,
                    'quantity' => $row['quantity'],
                    'purchase_unit' => $row['purchase_unit'],
                    'units_per_purchase_unit' => $row['units_per_purchase_unit'],
                    'stock_quantity' => $row['stock_quantity'],
                    'purchase_price' => $row['purchase_price'],
                    'sale_price' => $row['base_sale_price'],
                    'strip_sale_price' => $row['strip_sale_price'] ?? null,
                    'line_total' => round($row['quantity'] * $row['purchase_price'], 2),
                ]);

                StockMovement::create([
                    'product_id' => $row['product_id'],
                    'medicine_batch_id' => $batch->id,
                    'user_id' => $request->user()->id,
                    'type' => 'purchase',
                    'quantity_change' => $row['stock_quantity'],
                    'reference_type' => $item->getMorphClass(),
                    'reference_id' => $item->id,
                    'notes' => "Received through {$purchase->invoice_number}",
                ]);
            }

            app(AccountingPostingService::class)->postPurchase($purchase->load('supplier'), $request->user());

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase received and stock updated successfully.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product', 'items.batch']);

        return view('admin.purchases.show', compact('purchase'));
    }

    private function invoiceNumber(): string
    {
        do {
            $number = 'PUR-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Purchase::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
