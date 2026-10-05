<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\MedicineBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', 'in:all,low'],
            'letter' => ['nullable', 'regex:/^[A-Za-z]$/'],
            'company' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $products = Product::query()
            ->with(['genericName:id,name', 'batches.supplier:id,name', 'batches' => fn ($query) => $query
                ->where('quantity_available', '>', 0)
                ->where(fn ($expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today()))
                ->orderByRaw('expires_on IS NULL')->orderBy('expires_on')->orderBy('id')])
            ->where('is_active', true)
            ->when($data['letter'] ?? null, fn ($query, string $letter) => $query->where('name', 'like', $letter.'%'))
            ->when($data['company'] ?? null, fn ($query, string $company) => $query->whereHas('batches.supplier', fn ($supplier) => $supplier->where('name', $company)))
            ->when($data['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereHas('genericName', fn ($generic) => $generic->where('name', 'like', "%{$search}%"));
                });
            })
            ->whereHas('batches', fn ($query) => $query
                ->where('quantity_available', '>', 0)
                ->where(fn ($expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today())))
            ->orderBy('name')
            ->paginate($data['per_page'] ?? 20);

        $items = $products->getCollection()->map(function (Product $product) {
            $pieceStock = (int) $product->batches->sum('quantity_available');
            $stripStock = (int) $product->batches
                ->whereNotNull('strip_sale_price')
                ->sum(fn ($batch) => intdiv($batch->quantity_available, max(1, $product->pieces_per_strip)));
            $pieceBatch = $product->batches->first(fn ($batch) => $batch->sale_price !== null);
            $stripBatch = $product->batches->first(fn ($batch) => $batch->strip_sale_price !== null);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'generic_name' => $product->genericName?->name,
                'category' => $product->category,
                'supplier' => $product->batches->first()?->supplier?->name,
                'pieces_per_strip' => $product->pieces_per_strip,
                'sell_by_piece' => $product->sell_by_piece,
                'sell_by_strip' => $product->sell_by_strip,
                'piece_stock' => $pieceStock,
                'strip_stock' => $stripStock,
                'reorder_level' => $product->reorder_level,
                'is_low_stock' => $pieceStock <= $product->reorder_level,
                'piece_price' => $pieceBatch ? (float) $pieceBatch->sale_price : null,
                'strip_price' => $stripBatch ? (float) $stripBatch->strip_sale_price : null,
                'batches' => $product->batches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'piece_stock' => (int) $batch->quantity_available,
                    'piece_price' => (float) $batch->sale_price,
                    'strip_price' => $batch->strip_sale_price !== null ? (float) $batch->strip_sale_price : null,
                    'expires_on' => $batch->expires_on?->toDateString(),
                ])->values(),
            ];
        });

        if (($data['stock'] ?? 'all') === 'low') {
            $items = $items->where('is_low_stock', true)->values();
        }

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function companies(): JsonResponse
    {
        $companies = MedicineBatch::query()
            ->join('suppliers', 'suppliers.id', '=', 'medicine_batches.supplier_id')
            ->join('products', 'products.id', '=', 'medicine_batches.product_id')
            ->where('products.is_active', true)
            ->where('medicine_batches.quantity_available', '>', 0)
            ->where(fn ($expiry) => $expiry->whereNull('medicine_batches.expires_on')->orWhereDate('medicine_batches.expires_on', '>', today()))
            ->orderBy('suppliers.name')
            ->distinct()
            ->pluck('suppliers.name')
            ->values();

        return response()->json(['data' => $companies]);
    }
}
