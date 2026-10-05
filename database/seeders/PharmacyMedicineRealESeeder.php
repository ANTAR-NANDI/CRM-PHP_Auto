<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineRealESeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['E-Cap Plus', 'Vitamin C + Vitamin E', '250 mg + 200 mg', 'Capsule/Tablet', 'Drug International Ltd.'],
            ['E-capro', 'Aminocaproic Acid', '1 gm/5 ml', 'Syrup/Solution', 'Edruc Limited'],
            ['E-Cod', 'Multivitamin + Cod Liver Oil', 'N/A', 'Capsule/Syrup', 'Novo Healthcare and Pharma Ltd.'],
            ['E-Cof', 'Pseudoephedrine + Guaifenesin + Triprolidine', '30 mg + 100 mg + 1.25 mg/5 ml', 'Syrup', 'Edruc Limited'],
            ['E-Cof Plus', 'Guaifenesin + Levomenthol + Diphenhydramine', '100 mg + 1.1 mg + 14 mg/5 ml', 'Syrup', 'Edruc Limited'],
            ['E-coxib', 'Etoricoxib', '60 mg', 'Tablet', 'Drug International Ltd.'],
            ['E-coxib', 'Etoricoxib', '90 mg', 'Tablet', 'Drug International Ltd.'],
            ['E-coxib', 'Etoricoxib', '120 mg', 'Tablet', 'Drug International Ltd.'],
            ['E-Doxy', 'Doxycycline Hydrochloride', '100 mg', 'Capsule', 'Edruc Limited'],
            ['E-Fenac', 'Diclofenac Sodium', '50 mg', 'Tablet', 'Reliance Pharmaceuticals Ltd.'],
            ['E-Flu', 'Flucloxacillin Sodium', '125 mg/5 ml', 'Suspension', 'Edruc Limited'],
            ['E-Flu', 'Flucloxacillin Sodium', '500 mg', 'Capsule', 'Edruc Limited'],
            ['E-Hexin', 'Bromhexine Hydrochloride', '4 mg/5 ml', 'Syrup', 'Ethical Drugs Limited'],
            ['E-Hexin', 'Bromhexine Hydrochloride', '16 mg', 'Tablet', 'Ethical Drugs Limited'],
            ['E-Lax', 'Lactulose', '3.35 gm/5 ml', 'Syrup', 'Edruc Limited'],
            ['E-Lid', 'Erythromycin', '125 mg/5 ml', 'Suspension', 'Syntho Laboratories Ltd.'],
            ['E-Max MUPS', 'Esomeprazole', '20 mg', 'MUPS Tablet', 'Biogen Pharmaceuticals Ltd.'],
            ['E-mox', 'Amoxicillin Trihydrate', '125 mg/5 ml', 'Suspension', 'Edruc Limited'],
            ['E-mox', 'Amoxicillin Trihydrate', '250 mg', 'Capsule', 'Edruc Limited'],
            ['E-mox', 'Amoxicillin Trihydrate', '500 mg', 'Capsule', 'Edruc Limited'],
            ['E-MUPS', 'Esomeprazole', '20 mg', 'MUPS Tablet', 'Kumudini Pharma Ltd.'],
            ['E-Pod', 'Cefpodoxime Proxetil', '80 mg/5 ml', 'Suspension', 'Edruc Limited'],
            ['E-Pod', 'Cefpodoxime Proxetil', '200 mg', 'Tablet', 'Edruc Limited'],
            ['E-Reb', 'Rabeprazole Sodium', '20 mg', 'Tablet', 'Ethical Drugs Limited'],
            ['E-Saline', 'Oral Rehydration Salt', '10.25 gm', 'Sachet', 'Edruc Limited'],
        ];

        foreach ($rows as $index => [$brand, $genericName, $strength, $form, $supplierName]) {
            $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
            $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
            $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
            $category = $this->categoryFor($form);

            Product::updateOrCreate(
                ['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id],
                [
                    'barcode' => 'LSTER'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'brand_id' => $brandRecord->id,
                    'default_supplier_id' => $supplier->id,
                    'category' => $category,
                    'unit' => in_array($category, ['syrup', 'suspension'], true) ? 'bottle' : 'piece',
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
            str_contains($form, 'syrup'), str_contains($form, 'solution') => 'syrup',
            str_contains($form, 'sachet') => 'sachet',
            str_contains($form, 'capsule') => 'capsule',
            default => 'tablet',
        };
    }
}
