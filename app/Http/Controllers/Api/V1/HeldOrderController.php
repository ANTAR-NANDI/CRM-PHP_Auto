<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\PosSavedOrder;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HeldOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = PosSavedOrder::query()
            ->with('customer:id,name,phone')
            ->where('status', 'held')
            ->when(! $request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']), fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json(['data' => $orders->map(fn (PosSavedOrder $order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer?->name ?? 'Walking customer',
            'items_count' => count($order->items ?? []),
            'created_at' => $order->created_at?->toISOString(),
            'note' => $order->note,
        ])->values()]);
    }

    public function show(Request $request, PosSavedOrder $heldOrder): JsonResponse
    {
        abort_unless($heldOrder->status === 'held', 404);
        abort_unless($request->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']) || $heldOrder->user_id === $request->user()->id, 403);

        $productIds = collect($heldOrder->items ?? [])->pluck('product_id')->map(fn ($id) => (int) $id)->unique();
        $products = Product::query()->with(['genericName:id,name', 'batches' => fn ($query) => $query
            ->where('quantity_available', '>', 0)
            ->where(fn ($expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today()))
            ->orderByRaw('expires_on IS NULL')->orderBy('expires_on')->orderBy('id')])
            ->where('is_active', true)->whereIn('id', $productIds)->get()->keyBy('id');

        return response()->json(['data' => [
            'id' => $heldOrder->id,
            'order_number' => $heldOrder->order_number,
            'customer_type' => $heldOrder->customer_type,
            'customer' => $heldOrder->customer?->only(['id', 'name', 'phone', 'customer_type', 'due_balance']),
            'discount' => (float) $heldOrder->discount,
            'paid' => (float) $heldOrder->paid,
            'payment_method' => $heldOrder->payment_method,
            'items' => collect($heldOrder->items ?? [])->map(function (array $item) use ($products) {
                $product = $products->get((int) $item['product_id']);
                return $product ? [...$item, 'product' => $this->product($product)] : null;
            })->filter()->values(),
        ]]);
    }

    /**
     * Store a POS cart without selling it. Inventory is deliberately untouched
     * until the order is completed through the POS.
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $note = $request->string('note')->trim()->value();

        $order = PosSavedOrder::create([
            ...$data,
            'order_number' => 'HLD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'user_id' => $request->user()->id,
            'status' => 'held',
            'note' => blank($note) ? null : $note,
        ]);

        return response()->json([
            'message' => 'Sale held successfully. Stock has not been deducted.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ],
        ], 201);
    }

    private function product(Product $product): array
    {
        $pieceStock = (int) $product->batches->sum('quantity_available');
        $stripStock = (int) $product->batches->whereNotNull('strip_sale_price')->sum(fn ($batch) => intdiv($batch->quantity_available, max(1, $product->pieces_per_strip)));
        $pieceBatch = $product->batches->first(fn ($batch) => $batch->sale_price !== null);
        $stripBatch = $product->batches->first(fn ($batch) => $batch->strip_sale_price !== null);

        return [
            'id' => $product->id, 'name' => $product->name, 'barcode' => $product->barcode, 'generic_name' => $product->genericName?->name,
            'brand' => null, 'pieces_per_strip' => $product->pieces_per_strip, 'sell_by_piece' => $product->sell_by_piece, 'sell_by_strip' => $product->sell_by_strip,
            'piece_stock' => $pieceStock, 'strip_stock' => $stripStock, 'reorder_level' => $product->reorder_level, 'is_low_stock' => $pieceStock <= $product->reorder_level,
            'piece_price' => $pieceBatch ? (float) $pieceBatch->sale_price : null, 'strip_price' => $stripBatch ? (float) $stripBatch->strip_sale_price : null,
            'batches' => $product->batches->map(fn ($batch) => ['id' => $batch->id, 'piece_stock' => (int) $batch->quantity_available, 'piece_price' => (float) $batch->sale_price, 'strip_price' => $batch->strip_sale_price !== null ? (float) $batch->strip_sale_price : null, 'expires_on' => $batch->expires_on?->toDateString()])->values(),
        ];
    }
}
