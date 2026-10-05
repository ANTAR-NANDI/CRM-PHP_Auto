<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineListDSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['C-2', 'Cefuroxime Axetil', '250 mg', 'Tablet', 'Astra Biopharmaceuticals Ltd.'], ['C-2', 'Cefuroxime Axetil', '500 mg', 'Tablet', 'Astra Biopharmaceuticals Ltd.'], ['C-2', 'Cefuroxime Axetil', '125 mg/5 ml', 'Suspension', 'Astra Biopharmaceuticals Ltd.'], ['C-3', 'Cefixime Trihydrate', '400 mg', 'Capsule/Tablet', 'Astra Biopharmaceuticals Ltd.'], ['C-3', 'Cefixime Trihydrate', '100 mg/5 ml', 'Suspension', 'Astra Biopharmaceuticals Ltd.'], ['C-3', 'Cefixime Trihydrate', '200 mg', 'Capsule/Tablet', 'Astra Biopharmaceuticals Ltd.'], ['C-Bon', 'Vitamin C / Ascorbic Acid', '250 mg', 'Tablet', 'Ambee Pharmaceuticals Ltd.'], ['C-Zinc', 'Zinc Sulfate Monohydrate', '10 mg/5 ml', 'Syrup', 'Central Pharmaceuticals Ltd.'], ['Cab', 'Amlodipine Besilate', '5 mg', 'Tablet', 'ACI Limited'], ['Cabazol', 'Clotrimazole', '1%', 'Cream', 'Drug International Ltd.'], ['Caber', 'Cabergoline', '0.5 mg', 'Tablet', 'ACI Limited'], ['Cabergol', 'Cabergoline', '0.5 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'], ['Caberol', 'Cabergoline', '0.5 mg', 'Tablet', 'Square Pharmaceuticals PLC'], ['Cabonate', 'Calcium Carbonate', '500 mg', 'Tablet', 'Millat Pharmaceuticals Ltd.'], ['Cabretol', 'Carbamazepine', '200 mg', 'Tablet', 'Renata PLC'], ['Cadiar D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Maks Drug Limited'], ['Cadmin-D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'General Pharmaceuticals Ltd.'], ['Cadolin', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Jayson Pharmaceutical Ltd.'], ['Caf-N', 'Paracetamol + Caffeine', '500 mg + 65 mg', 'Tablet', 'Globex Pharmaceuticals Ltd.'], ['Cafedon', 'Paracetamol + Caffeine', '500 mg + 65 mg', 'Tablet', 'Healthcare Pharmaceuticals Ltd.'], ['Caid', 'Aspirin', '75 mg', 'Tablet', 'Jayson Pharmaceutical Ltd.'], ['Cal', 'Calcium Carbonate', '500 mg', 'Tablet', 'Pacific Pharmaceuticals Ltd.'], ['Cal D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Pacific Pharmaceuticals Ltd.'], ['Calbo', 'Calcium Carbonate', '500 mg', 'Tablet', 'Square Pharmaceuticals PLC'], ['Calbo-D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Square Pharmaceuticals PLC'],
        ];

        foreach ($rows as $index => [$brand, $genericName, $strength, $form, $supplierName]) {
            $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
            $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
            $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
            $category = str_contains(strtolower($form), 'suspension') ? 'suspension' : (str_contains(strtolower($form), 'cream') ? 'cream' : (str_contains(strtolower($form), 'capsule') ? 'capsule' : 'tablet'));
            Product::updateOrCreate(['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id], ['barcode' => 'LSTD'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT), 'brand_id' => $brandRecord->id, 'default_supplier_id' => $supplier->id, 'category' => $category, 'unit' => $category === 'suspension' ? 'bottle' : 'piece', 'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1, 'sell_by_piece' => true, 'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true), 'reorder_level' => 10, 'is_active' => true]);
        }
    }
}
