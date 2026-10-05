<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * @param  array{
     *     items: array<int, array{product_id: int, sale_unit: string, quantity: int}>,
     *     customer_type: string,
     *     customer_id?: int|null,
     *     discount?: float|int|string|null,
     *     paid: float|int|string,
     *     payment_method: string
     * }  $data
     */
    public function create(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $subtotal = 0.0;
            $allocatedItems = [];
            $customer = $this->resolveCustomer($data);

            foreach ($data['items'] as $index => $row) {
                $product = Product::query()->where('is_active', true)->find($row['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(["items.{$index}.product_id" => 'Select an active product.']);
                }

                $isStrip = $row['sale_unit'] === 'strip';
                if (($isStrip && ! $product->sell_by_strip) || (! $isStrip && ! $product->sell_by_piece)) {
                    throw ValidationException::withMessages(["items.{$index}.sale_unit" => "{$product->name} cannot be sold by {$row['sale_unit']}."]);
                }

                $multiplier = $isStrip ? max(1, $product->pieces_per_strip) : 1;
                $remainingUnits = (int) $row['quantity'];
                $batches = MedicineBatch::query()
                    ->where('product_id', $product->id)
                    ->where('quantity_available', '>', 0)
                    ->where(fn ($expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today()))
                    ->orderByRaw('expires_on IS NULL')->orderBy('expires_on')->orderBy('id')
                    ->lockForUpdate()->get();

                foreach ($batches as $batch) {
                    $availableUnits = $isStrip ? intdiv($batch->quantity_available, $multiplier) : $batch->quantity_available;
                    $price = $isStrip ? $batch->strip_sale_price : $batch->sale_price;
                    if ($availableUnits < 1 || $price === null) {
                        continue;
                    }

                    $takeUnits = min($remainingUnits, $availableUnits);
                    $stockQuantity = $takeUnits * $multiplier;
                    $lineTotal = round($takeUnits * (float) $price);
                    $batch->decrement('quantity_available', $stockQuantity);
                    $allocatedItems[] = [
                        'product_id' => $product->id,
                        'medicine_batch_id' => $batch->id,
                        'quantity' => $takeUnits,
                        'sale_unit' => $row['sale_unit'],
                        'units_per_sale_unit' => $multiplier,
                        'stock_quantity' => $stockQuantity,
                        'unit_purchase_price' => round((float) $batch->purchase_price * $multiplier),
                        'unit_sale_price' => $price,
                        'line_total' => $lineTotal,
                    ];
                    $subtotal += $lineTotal;
                    $remainingUnits -= $takeUnits;

                    if ($remainingUnits === 0) {
                        break;
                    }
                }

                if ($remainingUnits > 0) {
                    throw ValidationException::withMessages(["items.{$index}.quantity" => "Not enough {$row['sale_unit']} stock for {$product->name}."]);
                }
            }

            $subtotal = round($subtotal);
            $discount = round((float) ($data['discount'] ?? 0));
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount' => 'Discount cannot be greater than the subtotal.']);
            }

            $total = round($subtotal - $discount);
            $paid = round((float) $data['paid']);
            if ($paid < $total && $data['customer_type'] === 'walking') {
                throw ValidationException::withMessages(['paid' => 'Walk-in sales must be paid in full.']);
            }

            $due = max(0, round($total - $paid));
            if ($customer && $due > 0) {
                $customer->increment('due_balance', $due);
            }

            $sale = Sale::create([
                'invoice_number' => $this->invoiceNumber(),
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'customer_type' => $data['customer_type'],
                'cash_session_id' => null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'due' => $due,
                'payment_method' => $data['payment_method'],
                'sold_at' => now(),
            ]);

            $cost = 0.0;
            foreach ($allocatedItems as $row) {
                $item = $sale->items()->create($row);
                $cost += round((float) $row['unit_purchase_price'] * (int) $row['quantity'], 2);
                StockMovement::create([
                    'product_id' => $row['product_id'],
                    'medicine_batch_id' => $row['medicine_batch_id'],
                    'user_id' => $user->id,
                    'type' => 'sale',
                    'quantity_change' => -$row['stock_quantity'],
                    'reference_type' => $item->getMorphClass(),
                    'reference_id' => $item->id,
                    'notes' => "Sold through {$sale->invoice_number}",
                ]);
            }

            app(AccountingPostingService::class)->postSale($sale->load('customer'), round($cost), $user);

            return $sale->load(['user:id,name,employee_code', 'customer:id,name,customer_type,phone', 'items.product:id,name']);
        });
    }

    private function resolveCustomer(array $data): ?Customer
    {
        if ($data['customer_type'] === 'walking') {
            return null;
        }

        $customer = Customer::query()->where('is_active', true)->lockForUpdate()->find($data['customer_id'] ?? null);
        if (! $customer || $customer->customer_type !== $data['customer_type']) {
            throw ValidationException::withMessages(['customer_id' => 'Select an active customer matching the selected customer type.']);
        }

        return $customer;
    }

    private function invoiceNumber(): string
    {
        do {
            $number = 'SAL-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Sale::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
