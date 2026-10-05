<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\GenericName;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Creates a POS-ready catalogue of 5,000 total products for performance testing.
 *
 * This is intentionally separate from DatabaseSeeder: it creates clinically named
 * demo medicines and direct inventory batches, not real purchase/accounting entries.
 */
class PharmacyLoadTest5000Seeder extends Seeder
{
    private const TARGET_PRODUCT_COUNT = 5000;

    public function run(): void
    {
        $genericNames = GenericName::query()->where('is_active', true)->orderBy('id')->pluck('name', 'id');
        $genericIds = $genericNames->keys()->values();
        $brandIds = Brand::query()->where('is_active', true)->orderBy('id')->pluck('id');
        $supplierIds = Supplier::query()->where('is_active', true)->orderBy('id')->pluck('id');

        if ($genericNames->isEmpty() || $brandIds->isEmpty() || $supplierIds->isEmpty()) {
            throw new \LogicException('Run the supplier, brand, and generic-name seeders before creating the 5,000-medicine load-test catalogue.');
        }

        $existingNonLoadProducts = Product::query()->where('barcode', 'not like', 'LOAD5000-%')->count();
        $loadTestProductsNeeded = max(0, self::TARGET_PRODUCT_COUNT - $existingNonLoadProducts);
        $presentations = $this->presentations();

        foreach (collect(range(1, $loadTestProductsNeeded))->chunk(500) as $numbers) {
            $rows = $numbers->map(fn (int $number) => $this->productRow($number, $presentations, $genericIds, $genericNames, $brandIds, $supplierIds));
            $barcodes = $rows->pluck('barcode');

            // Upsert also replaces names from earlier versions of this seeder that
            // used "Load Test" labels, without creating duplicate products.
            DB::table('products')->upsert($rows->all(), ['barcode'], [
                'name', 'category', 'generic_name_id', 'brand_id', 'default_supplier_id',
                'unit', 'pieces_per_strip', 'sell_by_piece', 'sell_by_strip',
                'reorder_level', 'is_active', 'updated_at',
            ]);

            $products = Product::query()->whereIn('barcode', $barcodes)->get(['id', 'barcode', 'default_supplier_id', 'category', 'pieces_per_strip']);
            $existingBatches = DB::table('medicine_batches')->whereIn('product_id', $products->pluck('id'))->pluck('product_id')->all();
            $batches = $products->reject(fn (Product $product) => in_array($product->id, $existingBatches, true))
                ->map(function (Product $product): array {
                    $number = (int) substr($product->barcode, -5);
                    $isStrip = in_array($product->category, ['tablet', 'capsule'], true);
                    $unitCost = $this->unitCost($product->category, $number);
                    $salePrice = round($unitCost * 1.30, 2);

                    return [
                        'product_id' => $product->id,
                        'supplier_id' => $product->default_supplier_id,
                        'batch_number' => 'LOAD-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                        'expires_on' => now()->addMonths(18 + ($number % 18))->toDateString(),
                        'quantity_received' => $isStrip ? 500 : 100,
                        'quantity_available' => $isStrip ? 500 : 100,
                        'purchase_price' => $unitCost,
                        'sale_price' => $salePrice,
                        'strip_sale_price' => $isStrip ? round($salePrice * max(1, $product->pieces_per_strip), 2) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

            if ($batches->isNotEmpty()) {
                DB::table('medicine_batches')->insert($batches->all());
            }
        }

        $this->command?->info('POS load-test catalogue is ready: '.Product::count().' total products.');
    }

    private function idAt(Collection $ids, int $number): int
    {
        return (int) $ids[($number - 1) % $ids->count()];
    }

    private function productRow(int $number, array $presentations, Collection $genericIds, Collection $genericNames, Collection $brandIds, Collection $supplierIds): array
    {
        $genericId = $this->idAt($genericIds, $number);
        $genericName = (string) $genericNames[$genericId];
        // Each generic receives a different presentation before it repeats. This
        // produces unique, meaningful POS names instead of serial-number labels.
        $presentationNumber = intdiv($number - 1, $genericNames->count());
        $presentation = $presentations[$presentationNumber % count($presentations)];
        $category = $presentation['category'];
        $isStrip = in_array($category, ['tablet', 'capsule'], true);
        $packSuffix = $presentationNumber >= count($presentations)
            ? ' — Pack '.(intdiv($presentationNumber, count($presentations)) + 1)
            : '';

        return [
            'name' => "{$genericName} {$presentation['strength']} {$presentation['form']}{$packSuffix}",
            'category' => $category,
            'barcode' => 'LOAD5000-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
            'generic_name_id' => $genericId,
            'brand_id' => $this->idAt($brandIds, $number),
            'default_supplier_id' => $this->idAt($supplierIds, $number),
            'unit' => match ($category) {
                'syrup', 'suspension' => 'bottle',
                'injection' => 'vial',
                'cream' => 'tube',
                default => 'piece',
            },
            'pieces_per_strip' => $isStrip ? 10 : 1,
            'sell_by_piece' => true,
            'sell_by_strip' => $isStrip,
            'reorder_level' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Common POS presentations used only to make the performance catalogue easy
     * to search and recognise. Actual catalogue imports should use exact labels.
     *
     * @return array<int, array{strength: string, form: string, category: string}>
     */
    private function presentations(): array
    {
        return [
            ['strength' => '5 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '10 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '20 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '50 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '100 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '250 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '500 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '650 mg', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '5 mg', 'form' => 'Capsule', 'category' => 'capsule'],
            ['strength' => '20 mg', 'form' => 'Capsule', 'category' => 'capsule'],
            ['strength' => '100 mg', 'form' => 'Capsule', 'category' => 'capsule'],
            ['strength' => '250 mg', 'form' => 'Capsule', 'category' => 'capsule'],
            ['strength' => '500 mg', 'form' => 'Capsule', 'category' => 'capsule'],
            ['strength' => '1 g', 'form' => 'Tablet', 'category' => 'tablet'],
            ['strength' => '2 mg/5 ml', 'form' => 'Syrup', 'category' => 'syrup'],
            ['strength' => '5 mg/5 ml', 'form' => 'Syrup', 'category' => 'syrup'],
            ['strength' => '10 mg/5 ml', 'form' => 'Syrup', 'category' => 'syrup'],
            ['strength' => '100 mg/5 ml', 'form' => 'Suspension', 'category' => 'suspension'],
            ['strength' => '125 mg/5 ml', 'form' => 'Suspension', 'category' => 'suspension'],
            ['strength' => '200 mg/5 ml', 'form' => 'Suspension', 'category' => 'suspension'],
            ['strength' => '250 mg/5 ml', 'form' => 'Suspension', 'category' => 'suspension'],
            ['strength' => '500 mg/vial', 'form' => 'Injection', 'category' => 'injection'],
            ['strength' => '1 g/vial', 'form' => 'Injection', 'category' => 'injection'],
            ['strength' => '1%', 'form' => 'Cream', 'category' => 'cream'],
            ['strength' => '2%', 'form' => 'Cream', 'category' => 'cream'],
        ];
    }

    private function unitCost(string $category, int $number): float
    {
        return match ($category) {
            'tablet', 'capsule' => 4.50 + ($number % 8),
            'syrup', 'suspension' => 45 + ($number % 60),
            'injection' => 70 + ($number % 80),
            default => 55 + ($number % 50),
        };
    }
}
