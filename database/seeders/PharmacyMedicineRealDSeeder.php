<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineRealDSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['D Metro', 'Metronidazole', '200 mg/5 ml', 'Suspension', 'Desh Pharmaceuticals Ltd.'],
            ['D Metro', 'Metronidazole', '400 mg', 'Tablet', 'Desh Pharmaceuticals Ltd.'],
            ['D-2000', 'Cholecalciferol / Vitamin D3', '2000 IU', 'Tablet/Capsule', 'The IBN SINA Pharmaceutical PLC'],
            ['D-20000', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'The IBN SINA Pharmaceutical PLC'],
            ['D-40000', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'The IBN SINA Pharmaceutical PLC'],
            ['D-Act', 'Calcitriol', '0.25 mcg', 'Capsule', 'Healthcare Pharmaceuticals Ltd.'],
            ['D-Balance', 'Cholecalciferol / Vitamin D3', '2000 IU', 'Tablet/Capsule', 'Square Pharmaceuticals PLC'],
            ['D-Balance', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'Square Pharmaceuticals PLC'],
            ['D-Balance', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'Square Pharmaceuticals PLC'],
            ['D-Balance', 'Cholecalciferol / Vitamin D3', '50000 IU', 'Capsule', 'Square Pharmaceuticals PLC'],
            ['D-Balance', 'Cholecalciferol / Vitamin D3', '200000 IU/ml', 'Oral Drop', 'Square Pharmaceuticals PLC'],
            ['D-Best', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'Pacific Pharmaceuticals Ltd.'],
            ['D-Best', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'Pacific Pharmaceuticals Ltd.'],
            ['D-Best', 'Cholecalciferol / Vitamin D3', '200000 IU/ml', 'Oral Drop', 'Pacific Pharmaceuticals Ltd.'],
            ['D-boost', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'C2C Pharma Ltd.'],
            ['D-boost', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'C2C Pharma Ltd.'],
            ['D-Build', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'DBL Pharmaceuticals Ltd.'],
            ['D-Build', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'DBL Pharmaceuticals Ltd.'],
            ['D-Cap', 'Cholecalciferol / Vitamin D3', '1000 IU', 'Tablet/Capsule', 'Drug International Ltd.'],
            ['D-Cap', 'Cholecalciferol / Vitamin D3', '2000 IU', 'Tablet/Capsule', 'Drug International Ltd.'],
            ['D-Cap', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'Drug International Ltd.'],
            ['D-Cap', 'Cholecalciferol / Vitamin D3', '40000 IU', 'Capsule', 'Drug International Ltd.'],
            ['D-Care', 'Cholecalciferol / Vitamin D3', '20000 IU', 'Capsule', 'The White Horse Pharmaceuticals Ltd.'],
            ['D-Cetamol', 'Paracetamol', '120 mg/5 ml', 'Suspension', 'Decent Pharma Laboratories Ltd.'],
            ['D-Cetamol', 'Paracetamol', '500 mg', 'Tablet', 'Decent Pharma Laboratories Ltd.'],
        ];

        foreach ($rows as $index => [$brand, $genericName, $strength, $form, $supplierName]) {
            $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
            $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
            $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
            $category = $this->categoryFor($form);

            Product::updateOrCreate(
                ['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id],
                [
                    'barcode' => 'LSTDR'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'brand_id' => $brandRecord->id,
                    'default_supplier_id' => $supplier->id,
                    'category' => $category,
                    'unit' => in_array($category, ['suspension', 'syrup', 'drops'], true) ? 'bottle' : 'piece',
                    'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1,
                    'sell_by_piece' => true,
                    'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true),
                    'reorder_level' => 10,
                    'is_active' => true,
                ]
            );
        }
    }

    private function categoryFor(string $form): string
    {
        $form = strtolower($form);

        return match (true) {
            str_contains($form, 'suspension') => 'suspension',
            str_contains($form, 'drop') => 'drops',
            str_contains($form, 'capsule') => 'capsule',
            default => 'tablet',
        };
    }
}
