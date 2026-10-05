<?php

namespace Database\Seeders;

use App\Models\GenericName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyMedicineCatalogSeeder extends Seeder
{
    /**
     * Generate five searchable medicine catalogue entries for every generic name.
     * Products intentionally have no stock or supplier: those are recorded by Purchase batches.
     */
    public function run(): void
    {
        // Kept for backwards-compatible artisan commands; synthetic catalogue generation is retired.
        $this->call(PharmacyMedicineListASeeder::class);

        return;

        if (GenericName::query()->where('is_active', true)->doesntExist()) {
            $this->call(PharmacyGenericNamesSeeder::class);
        }

        $variants = [
            ['suffix' => '100 mg Tablet', 'category' => 'tablet', 'unit' => 'piece', 'pieces_per_strip' => 10, 'sell_by_strip' => true],
            ['suffix' => '250 mg Tablet', 'category' => 'tablet', 'unit' => 'piece', 'pieces_per_strip' => 10, 'sell_by_strip' => true],
            ['suffix' => '500 mg Tablet', 'category' => 'tablet', 'unit' => 'piece', 'pieces_per_strip' => 10, 'sell_by_strip' => true],
            ['suffix' => '250 mg Capsule', 'category' => 'capsule', 'unit' => 'piece', 'pieces_per_strip' => 10, 'sell_by_strip' => true],
            ['suffix' => '60 ml Suspension', 'category' => 'suspension', 'unit' => 'bottle', 'pieces_per_strip' => 1, 'sell_by_strip' => false],
        ];

        $timestamp = now();
        $rows = [];
        $serial = 1;

        foreach (GenericName::query()->where('is_active', true)->orderBy('id')->cursor() as $generic) {
            foreach ($variants as $variant) {
                $rows[] = [
                    'name' => $generic->name.' '.$variant['suffix'],
                    'barcode' => (string) (8999000000000 + $serial++),
                    'generic_name_id' => $generic->id,
                    'category' => $variant['category'],
                    'unit' => $variant['unit'],
                    'pieces_per_strip' => $variant['pieces_per_strip'],
                    'sell_by_piece' => true,
                    'sell_by_strip' => $variant['sell_by_strip'],
                    'reorder_level' => 10,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('products')->insertOrIgnore($chunk);
        }

        $this->command?->info(count($rows).' medicine catalogue rows processed.');
    }
}
