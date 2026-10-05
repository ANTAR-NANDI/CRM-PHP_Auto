<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineRealKtoPSeeder extends Seeder
{
    public function run(): void
    {
        $catalogues = [
            'K' => [
                ['K-Fast', 'Ketoconazole', '2%', 'Tablet', 'Save Trading International'],
                ['K-Lac', 'Ketorolac Tromethamine', '10 mg', 'Tablet', 'Ethical Drugs Limited'],
                ['K-Lac', 'Ketorolac Tromethamine', '30 mg/ml', 'Injection', 'Ethical Drugs Limited'],
                ['K-One MM', 'Phytomenadione', '2 mg/0.2 ml', 'Injection', 'Square Pharmaceuticals PLC'],
                ['K-Pol', 'Paracetamol + Caffeine', '500 mg + 65 mg', 'Tablet', 'Modern Pharmaceuticals Ltd.'],
                ['K-Saline N', 'ORS', '10.25 gm', 'Sachet', 'Kemiko Pharmaceuticals Ltd.'],
                ['Kacin', 'Amikacin', '100 mg/2 ml', 'Injection', 'ACI Limited'],
                ['Kacin', 'Amikacin', '500 mg/2 ml', 'Injection', 'ACI Limited'],
                ['Kadol', 'Tramadol', '50 mg', 'Tablet', 'Kemiko Pharmaceuticals Ltd.'],
                ['Kamoxy', 'Amoxicillin', '500 mg', 'Tablet', 'Kemiko Pharmaceuticals Ltd.'],
            ],
            'L' => [
                ['L-Amlo', 'Levamlodipine', '2.5 mg', 'Tablet', 'Navana Pharmaceuticals Ltd.'],
                ['L-Amlo', 'Levamlodipine', '5 mg', 'Tablet', 'Navana Pharmaceuticals Ltd.'],
                ['L-con', 'Lomefloxacin', '0.3%', 'Eye Drop', 'Monicopharma Ltd.'],
                ['L-Sol', 'Levosalbutamol', '1 mg/5 ml', 'Syrup', 'Ethical Drugs Limited'],
                ['Labcal-D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Labaid Pharma Ltd.'],
                ['Labegest', 'Labetalol', '100 mg', 'Tablet', 'Incepta Pharmaceuticals Ltd.'],
                ['Laben DS', 'Albendazole', '400 mg', 'Tablet', 'Premier Pharmaceuticals Ltd.'],
                ['Labenac', 'Aceclofenac', '100 mg', 'Tablet', 'Labaid Pharma Ltd.'],
                ['Labpan', 'Pantoprazole', '40 mg', 'Tablet', 'Labaid Pharma Ltd.'],
                ['Laclose', 'Lactulose', '3.35 gm/5 ml', 'Syrup', 'Opsonin Pharma Ltd.'],
            ],
            'M' => [
                ['M Boss', 'Ambroxol', '15 mg/5 ml', 'Syrup', 'Central Pharmaceuticals Ltd.'],
                ['M-beg', 'Mirabegron', '25 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['M-Card', 'Amlodipine', '5 mg', 'Tablet', 'Zenith Pharmaceuticals Ltd.'],
                ['M-Dazole', 'Metronidazole', '400 mg', 'Tablet', 'Modern Pharmaceuticals Ltd.'],
                ['M-Form', 'Metformin', '850 mg', 'Tablet', 'Central Pharmaceuticals Ltd.'],
                ['M-Kast', 'Montelukast', '10 mg', 'Tablet', 'Drug International Ltd.'],
                ['M-lucas', 'Montelukast', '10 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['M-Pol', 'Paracetamol', '500 mg', 'Tablet', 'Modern Pharmaceuticals Ltd.'],
                ['M-Prazol', 'Esomeprazole', '20 mg', 'Tablet', 'Doctor TIMS Pharmaceuticals Ltd.'],
                ['M-Trim DS', 'Sulphamethoxazole + Trimethoprim', '800 mg + 160 mg', 'Tablet', 'Modern Pharmaceuticals Ltd.'],
            ],
            'N' => [
                ['N Hexin', 'Bromhexine', '4 mg/5 ml', 'Syrup', 'Nipa Pharmaceuticals Ltd.'],
                ['N Zith', 'Azithromycin', '500 mg', 'Tablet', 'Novus Pharmaceuticals Ltd.'],
                ['N-ASPA', 'Drotaverine', '40 mg', 'Tablet', 'Albion Laboratories Ltd.'],
                ['N-bion', 'Vitamin B1 + B6 + B12', '100 mg + 200 mg + 200 mcg', 'Tablet', 'Navana Pharmaceuticals Ltd.'],
                ['N-MAX', 'Ibuprofen + Paracetamol', '200 mg + 500 mg', 'Tablet', 'Beximco Pharmaceuticals Ltd.'],
                ['N-Proton', 'Naproxen + Esomeprazole', '500 mg + 20 mg', 'Tablet', 'Doctor TIMS Pharmaceuticals Ltd.'],
                ['Naafcal-D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Naafco Pharma PLC'],
                ['Naafco Orsaline', 'ORS', '10.25 gm', 'Sachet', 'Naafco Pharma PLC'],
                ['Naafzinc-DS', 'Zinc Sulfate', '20 mg', 'Tablet', 'Naafco Pharma PLC'],
                ['Nabu', 'Nabumetone', '500 mg', 'Tablet', 'ACI Limited'],
            ],
            'O' => [
                ['O Fruity Saline', 'ORS', '10 gm', 'Sachet', 'Zenith Pharmaceuticals Ltd.'],
                ['O Saline N', 'ORS', '10.25 gm', 'Sachet', 'Zenith Pharmaceuticals Ltd.'],
                ['O-20', 'Omeprazole', '20 mg', 'Tablet', 'Asiatic Laboratories Ltd.'],
                ['O-40', 'Omeprazole', '40 mg', 'Tablet', 'Asiatic Laboratories Ltd.'],
                ['O-Cal', 'Calcium Orotate', '740 mg', 'Tablet', 'ACME Laboratories Ltd.'],
                ['O3', 'Omega-3 Acid Ethyl Esters', '1000 mg', 'Capsule', 'Purnava Limited'],
                ['Oasis', 'Hypromellose', '0.3%', 'Eye Drop', 'UNIDO Pharmaceuticals Ltd.'],
                ['Obactin', 'Ofloxacin', '0.3%', 'Eye Drop', 'The IBN SINA Pharmaceutical PLC'],
                ['Obemet', 'Metformin', '500 mg', 'Tablet', 'Euro Pharma Ltd.'],
                ['Obila', 'Bilastine', '20 mg', 'Tablet', 'ACI Limited'],
            ],
            'P' => [
                ['P Lac', 'Lactulose', '3.35 gm/5 ml', 'Syrup', 'Pharmadesh Laboratories Ltd.'],
                ['P-20', 'Pantoprazole', '20 mg', 'Tablet', 'Asiatic Laboratories Ltd.'],
                ['P-40', 'Pantoprazole', '40 mg', 'Tablet', 'Asiatic Laboratories Ltd.'],
                ['P-Cef', 'Cephradine', '500 mg', 'Tablet', 'Pharmadesh Laboratories Ltd.'],
                ['P-Cef', 'Cephradine', '125 mg/5 ml', 'Suspension', 'Pharmadesh Laboratories Ltd.'],
                ['P-Cort', 'Prednisolone', '5 mg', 'Tablet', 'Globe Pharmaceuticals Ltd.'],
                ['P-Dol', 'Paracetamol + Tramadol', '325 mg + 37.5 mg', 'Tablet', 'Popular Pharmaceuticals PLC'],
                ['P-Don', 'Domperidone', '10 mg', 'Tablet', 'Pharmadesh Laboratories Ltd.'],
                ['P-Gut', 'Pantoprazole', '40 mg', 'Tablet', 'Globe Pharmaceuticals Ltd.'],
                ['P-Zink', 'Zinc Sulfate', '20 mg', 'Tablet', 'Popular Pharmaceuticals PLC'],
            ],
        ];

        foreach ($catalogues as $letter => $rows) {
            foreach ($rows as $index => [$brand, $genericName, $strength, $form, $supplierName]) {
                $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
                $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
                $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
                $category = $this->categoryFor($form);

                Product::updateOrCreate(
                    ['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id],
                    [
                        'barcode' => 'LST'.$letter.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                        'brand_id' => $brandRecord->id,
                        'default_supplier_id' => $supplier->id,
                        'category' => $category,
                        'unit' => in_array($category, ['syrup', 'suspension', 'drops'], true) ? 'bottle' : 'piece',
                        'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1,
                        'sell_by_piece' => true,
                        'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true),
                        'reorder_level' => 10,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function categoryFor(string $form): string
    {
        $form = strtolower($form);

        return match (true) {
            str_contains($form, 'injection') => 'injection',
            str_contains($form, 'suspension') => 'suspension',
            str_contains($form, 'syrup') => 'syrup',
            str_contains($form, 'drop') => 'drops',
            str_contains($form, 'sachet') => 'sachet',
            str_contains($form, 'capsule') => 'capsule',
            default => 'tablet',
        };
    }
}
