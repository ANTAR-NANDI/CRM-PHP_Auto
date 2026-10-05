<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AccountingPostingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Adds POS-ready purchase stock for active medicines without sellable stock.
 *
 * Run the catalogue seeders first. This seeder deliberately creates purchases
 * (rather than only batches) so stock, purchase, supplier due and accounting
 * reports all agree with one another.
 */
class PharmacyCatalogPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('is_active', true)->orderBy('id')->first();

        if (! $user) {
            throw new LogicException('Create an active admin/employee before running the catalogue purchase seeder.');
        }

        $requiredAccounts = ['1000101', '100010401', '2000101'];
        if (ChartOfAccount::query()->whereIn('code', $requiredAccounts)->count() !== count($requiredAccounts)) {
            throw new LogicException('Run ChartOfAccountsSeeder before running the catalogue purchase seeder.');
        }

        $fallbackSupplier = Supplier::query()->where('is_active', true)->orderBy('id')->first();
        if (! $fallbackSupplier) {
            throw new LogicException('Create an active supplier before running the catalogue purchase seeder.');
        }

        $products = Product::query()
            ->with('defaultSupplier')
            ->where('is_active', true)
            ->whereDoesntHave('batches', fn ($query) => $query
                ->where('quantity_available', '>', 0)
                ->where(fn ($expiry) => $expiry->whereNull('expires_on')->orWhereDate('expires_on', '>', today())))
            ->orderBy('default_supplier_id')
            ->orderBy('id')
            ->get()
            ->map(function (Product $product) use ($fallbackSupplier): Product {
                if (! $product->defaultSupplier) {
                    $product->setRelation('defaultSupplier', $fallbackSupplier);
                }

                return $product;
            });

        if ($products->isEmpty()) {
            $this->command?->info('Every active medicine already has sellable stock. No demo purchases were created.');

            return;
        }

        foreach ($products->groupBy(fn (Product $product) => $product->defaultSupplier->id) as $supplierProducts) {
            foreach ($supplierProducts->chunk(10) as $chunkNumber => $chunk) {
                $supplier = $chunk->first()->defaultSupplier;
                // Product IDs make this idempotent even when a later catalogue list
                // adds another group for a supplier that already has demo purchases.
                $invoiceNumber = sprintf('PUR-CATALOG-%d-%d', $supplier->id, $chunk->first()->id);

                if (Purchase::query()->where('invoice_number', $invoiceNumber)->exists()) {
                    continue;
                }

                DB::transaction(function () use ($chunk, $chunkNumber, $invoiceNumber, $supplier, $user): void {
                    $rows = $chunk->map(function (Product $product) use ($chunkNumber): array {
                        $seed = (int) sprintf('%u', crc32((string) $product->barcode));
                        $piecesPerStrip = max(1, (int) $product->pieces_per_strip);
                        $isStripProduct = $product->sell_by_strip && $piecesPerStrip > 1;
                        $unitCost = $this->unitCost($product, $seed);
                        $purchaseUnit = $isStripProduct ? 'strip' : 'piece';
                        $quantity = $isStripProduct ? 20 + ($seed % 21) : 15 + ($seed % 16);
                        $purchasePrice = $isStripProduct
                            ? round($unitCost * $piecesPerStrip, 2)
                            : $unitCost;
                        $singleSalePrice = round($unitCost * 1.35, 2);
                        $stripSalePrice = $isStripProduct
                            ? round($singleSalePrice * $piecesPerStrip, 2)
                            : null;

                        return compact(
                            'product', 'seed', 'piecesPerStrip', 'purchaseUnit', 'quantity',
                            'purchasePrice', 'unitCost', 'singleSalePrice', 'stripSalePrice'
                        );
                    });

                    $subtotal = round((float) $rows->sum(fn (array $row) => $row['quantity'] * $row['purchasePrice']), 2);
                    $paid = round($subtotal * 0.60, 2);
                    $purchase = Purchase::create([
                        'invoice_number' => $invoiceNumber,
                        'supplier_id' => $supplier->id,
                        'user_id' => $user->id,
                        'subtotal' => $subtotal,
                        'discount' => 0,
                        'total' => $subtotal,
                        'paid' => $paid,
                        'purchased_at' => now()->subDays(30 - min(25, $chunkNumber))->toDateString(),
                    ]);

                    foreach ($rows as $index => $row) {
                        /** @var Product $product */
                        $product = $row['product'];
                        $stockQuantity = $row['quantity'] * ($row['purchaseUnit'] === 'strip' ? $row['piecesPerStrip'] : 1);
                        $batch = MedicineBatch::create([
                            'product_id' => $product->id,
                            'supplier_id' => $supplier->id,
                            'batch_number' => sprintf('CAT-%d-%02d-%02d', $supplier->id, $chunkNumber + 1, $index + 1),
                            'expires_on' => now()->addMonths(18 + ($row['seed'] % 18))->toDateString(),
                            'quantity_received' => $stockQuantity,
                            'quantity_available' => $stockQuantity,
                            'purchase_price' => $row['unitCost'],
                            'sale_price' => $row['singleSalePrice'],
                            'strip_sale_price' => $row['stripSalePrice'],
                        ]);

                        $item = $purchase->items()->create([
                            'product_id' => $product->id,
                            'medicine_batch_id' => $batch->id,
                            'quantity' => $row['quantity'],
                            'purchase_unit' => $row['purchaseUnit'],
                            'units_per_purchase_unit' => $row['purchaseUnit'] === 'strip' ? $row['piecesPerStrip'] : 1,
                            'stock_quantity' => $stockQuantity,
                            'purchase_price' => $row['purchasePrice'],
                            'sale_price' => $row['singleSalePrice'],
                            'strip_sale_price' => $row['stripSalePrice'],
                            'line_total' => round($row['quantity'] * $row['purchasePrice'], 2),
                        ]);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'medicine_batch_id' => $batch->id,
                            'user_id' => $user->id,
                            'type' => 'purchase',
                            'quantity_change' => $stockQuantity,
                            'reference_type' => $item->getMorphClass(),
                            'reference_id' => $item->id,
                            'notes' => "Catalogue stock received through {$purchase->invoice_number}",
                        ]);
                    }

                    app(AccountingPostingService::class)->postPurchase($purchase->load('supplier'), $user);
                });
            }
        }
    }

    private function unitCost(Product $product, int $seed): float
    {
        return match ($product->category) {
            'tablet', 'capsule' => round(2.50 + (($seed % 20) * 0.50), 2),
            'syrup', 'suspension' => (float) (55 + ($seed % 7) * 10),
            'injection' => (float) (45 + ($seed % 8) * 15),
            'cream', 'ointment', 'gel' => (float) (65 + ($seed % 8) * 10),
            default => (float) (30 + ($seed % 10) * 8),
        };
    }
}
