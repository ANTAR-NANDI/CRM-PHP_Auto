<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineRealFtoJSeeder extends Seeder
{
    public function run(): void
    {
        $catalogues = [
            'F' => [
                ['F-Gurd', 'Betamethasone + Clotrimazole', '0.05% + 1%', 'Cream', 'Ethical Drugs Limited'],
                ['F-Lon', 'Fluorometholone Acetate', '0.1%', 'Eye Drop', 'Drug International Ltd.'],
                ['F-Pro', 'Fluticasone Propionate', '50 mcg/spray', 'Nasal Spray', 'Drug International Ltd.'],
                ['F-Pro Plus', 'Azelastine + Fluticasone', '137 mcg + 50 mcg/spray', 'Nasal Spray', 'Drug International Ltd.'],
                ['F-Son', 'Fluticasone Furoate', '27.5 mcg/spray', 'Nasal Spray', 'Drug International Ltd.'],
                ['F-Zol', 'Fluconazole', '50 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['F-Zol', 'Fluconazole', '150 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['F-Zol', 'Fluconazole', '200 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['F-Zol', 'Fluconazole', '50 mg/5 ml', 'Suspension', 'Popular Pharmaceuticals Ltd.'],
                ['F+F', 'Ferrous Sulfate + Folic Acid', '150 mg + 0.5 mg', 'Tablet', 'Zenith Pharmaceuticals Ltd.'],
                ['Fabetor', 'Etoricoxib', '60 mg', 'Tablet', 'Radiant Pharmaceuticals Ltd.'],
                ['Fabetor', 'Etoricoxib', '90 mg', 'Tablet', 'Radiant Pharmaceuticals Ltd.'],
                ['Fabetor', 'Etoricoxib', '120 mg', 'Tablet', 'Radiant Pharmaceuticals Ltd.'],
                ['Facid', 'Sodium Fusidate', '2%', 'Topical', 'Eskayef Pharmaceuticals Ltd.'],
                ['Facid', 'Sodium Fusidate', '250 mg', 'Oral', 'Eskayef Pharmaceuticals Ltd.'],
                ['Facid BT', 'Fusidic Acid + Betamethasone', '2% + 0.1%', 'Topical', 'Eskayef Pharmaceuticals Ltd.'],
                ['Facid HC', 'Fusidic Acid + Hydrocortisone', '2% + 1%', 'Topical', 'Eskayef Pharmaceuticals Ltd.'],
                ['Facticin', 'Gemifloxacin', '320 mg', 'Tablet', 'Square Pharmaceuticals PLC'],
                ['Factiq', 'Gemifloxacin', '320 mg', 'Tablet', 'Monicopharma Ltd.'],
                ['Falcon', 'Fluconazole', '50 mg', 'Tablet', 'Ethical Drugs Limited'],
                ['Falcon', 'Fluconazole', '150 mg', 'Tablet', 'Ethical Drugs Limited'],
                ['Falcon', 'Fluconazole', '50 mg/5 ml', 'Suspension', 'Ethical Drugs Limited'],
                ['Famas', 'Famotidine', '20 mg', 'Tablet', 'The IBN SINA Pharmaceutical PLC'],
                ['Famas', 'Famotidine', '40 mg', 'Tablet', 'The IBN SINA Pharmaceutical PLC'],
                ['Famicef', 'Cefuroxime Axetil', '250 mg', 'Tablet', 'ACME Laboratories Ltd.'],
            ],
            'G' => [
                ['G-Adrenaline', 'Adrenaline', '1 mg/ml', 'Injection', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Albendazole', 'Albendazole', '400 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amlo', 'Amlodipine Besilate', '5 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amlo', 'Amlodipine Besilate', '10 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amoxicillin', 'Amoxicillin', '250 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amoxicillin', 'Amoxicillin', '500 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amoxicillin', 'Amoxicillin', '125 mg/5 ml', 'Suspension', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Amoxicillin', 'Amoxicillin', '125 mg/1.25 ml', 'Drop', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Antacid', 'Aluminium Hydroxide + Magnesium Hydroxide', '250 mg + 500 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Antacid MH', 'Aluminium Hydroxide + Magnesium Hydroxide + Simethicone', '400 mg + 400 mg + 30 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Antihistamine', 'Chlorpheniramine Maleate', '4 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Antihistamine', 'Chlorpheniramine Maleate', '2 mg/5 ml', 'Syrup', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Antiseptic', 'Chlorhexidine + Cetrimide', '0.3% + 3%', 'Topical', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Aspirin', 'Aspirin', '300 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Atorvast', 'Atorvastatin', '10 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Atorvast', 'Atorvastatin', '20 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Atropine', 'Atropine Sulfate', '0.6 mg/ml', 'Injection', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Azithromycin', 'Azithromycin', '500 mg', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-B Benzoate', 'Benzyl Benzoate', '25% w/v', 'Topical', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Benzathine Penicil', 'Benzathine Benzylpenicillin', '12 lac units/vial', 'Injection', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Benzathine Penicil', 'Benzathine Benzylpenicillin', '6 lac units/vial', 'Injection', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Benzosal', 'Benzoic Acid + Salicylic Acid', '6% + 3%', 'Topical', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Cal D', 'Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Gonoshasthaya Pharma Ltd.'],
                ['G-Calbo', 'Algae Calcium + Vitamin D3', '500 mg + 200 IU', 'Tablet', 'Square Pharmaceuticals PLC'],
                ['G-Calbo DX', 'Algae Calcium + Vitamin D3', '600 mg + 400 IU', 'Tablet', 'Square Pharmaceuticals PLC'],
            ],
            'H' => [
                ['H Ben', 'Albendazole', '400 mg', 'Tablet', 'Hudson Pharmaceuticals Ltd.'],
                ['H-CIN', 'Chlorpheniramine Maleate', '4 mg', 'Tablet', 'Pharmik Laboratories Ltd.'],
                ['H-Nap', 'Naproxen Sodium', '500 mg', 'Tablet', 'Hudson Pharmaceuticals Ltd.'],
                ['H-Pen', 'Phenoxymethyl Penicillin', '250 mg', 'Tablet', 'Hudson Pharmaceuticals Ltd.'],
                ['H-Quin', 'Hydroxychloroquine Sulphate', '200 mg', 'Tablet', 'Eskayef Pharmaceuticals Ltd.'],
                ['H-Selax', 'Salbutamol', '4 mg', 'Tablet', 'Hudson Pharmaceuticals Ltd.'],
                ['H-Selax', 'Salbutamol', '2 mg/5 ml', 'Syrup', 'Hudson Pharmaceuticals Ltd.'],
                ['H-Trimazole', 'Clotrimazole + Hydrocortisone', '1% + 1%', 'Cream', 'Opsonin Pharma Ltd.'],
                ['H2O Moisturising Lotion', 'Miscellaneous Topical Agents', 'N/A', 'Lotion', 'ZAS Corporation'],
                ['Haemee', 'Ferric Maltol', '30 mg', 'Tablet', 'C2C Pharma Ltd.'],
                ['Haemozin TR', 'Ferrous Sulfate + Folic Acid + Zinc', '150 mg + 0.5 mg + 61.8 mg', 'Tablet', 'Doctor’s Chemical Works Ltd.'],
                ['Hairgain', 'Minoxidil', '2%', 'Topical', 'UniMed UniHealth Pharmaceuticals Ltd.'],
                ['Hairgain', 'Minoxidil', '5%', 'Topical', 'UniMed UniHealth Pharmaceuticals Ltd.'],
                ['Hairgrow', 'Minoxidil', '2%', 'Topical', 'Eskayef Pharmaceuticals Ltd.'],
                ['Hairgrow', 'Minoxidil', '5%', 'Topical', 'Eskayef Pharmaceuticals Ltd.'],
                ['Halaven', 'Eribulin Mesylate', '0.44 mg/ml', 'Injection', 'Radiant Pharmaceuticals Ltd.'],
                ['Halobet', 'Halobetasol Propionate', '0.05%', 'Topical', 'Square Pharmaceuticals PLC'],
                ['Halocort', 'Halobetasol Propionate', '0.05%', 'Topical', 'ACI Limited'],
                ['Halonate', 'Halobetasol Propionate', '0.05%', 'Topical', 'Healthcare Pharmaceuticals Ltd.'],
                ['Halop', 'Haloperidol', '5 mg', 'Tablet', 'Opsonin Pharma Ltd.'],
                ['Halopen', 'Flucloxacillin Sodium', '250 mg', 'Tablet', 'NIPRO JMI Pharma Ltd.'],
                ['Halopen', 'Flucloxacillin Sodium', '500 mg', 'Tablet', 'NIPRO JMI Pharma Ltd.'],
                ['Halopen', 'Flucloxacillin Sodium', '125 mg/5 ml', 'Suspension', 'NIPRO JMI Pharma Ltd.'],
                ['Halopid', 'Haloperidol', '5 mg', 'Tablet', 'Incepta Pharmaceuticals Ltd.'],
                ['Halopid', 'Haloperidol', '5 mg/ml', 'Injection', 'Incepta Pharmaceuticals Ltd.'],
            ],
            'I' => [
                ['I Care', 'Vitamin C + Vitamin E + Lutein + Copper + Zinc', 'Eye supplement', 'Other', 'Pacific Pharmaceuticals Ltd.'],
                ['I-Aqua', 'Carboxymethylcellulose Sodium', '1%', 'Eye Drop', 'Apex Pharma Ltd.'],
                ['I-Cin', 'Indomethacin', '25 mg', 'Tablet', 'Indo Bangla Pharmaceutical'],
                ['I-Clo', 'Dexamethasone + Chloramphenicol', '0.1% + 0.5%', 'Eye Drop', 'Ad-din Pharmaceuticals Ltd.'],
                ['I-Fol', 'Ferrous Fumarate + Folic Acid', '200 mg + 200 mcg', 'Tablet', 'Indo Bangla Pharmaceutical'],
                ['I-Fort', 'Polyethylene Glycol + Propylene Glycol', '0.4% + 0.3%', 'Eye Drop', 'Biopharma Limited'],
                ['I-Gold', 'Vitamin C + Vitamin E + Lutein + Copper + Zinc', 'Eye supplement', 'Other', 'Aristopharma Ltd.'],
                ['I-Guard', 'Chloramphenicol', '0.5%', 'Eye Drop', 'Incepta Pharmaceuticals Ltd.'],
                ['I-Moist', 'Polyethylene Glycol + Propylene Glycol', '0.4% + 0.3%', 'Eye Drop', 'Drug International Ltd.'],
                ['I-Penam', 'Meropenem', '500 mg/vial', 'Injection', 'Incepta Pharmaceuticals Ltd.'],
                ['I-Penam', 'Meropenem', '1 gm/vial', 'Injection', 'Incepta Pharmaceuticals Ltd.'],
                ['I-Penam', 'Meropenem', '250 mg/vial', 'Injection', 'Incepta Pharmaceuticals Ltd.'],
                ['I-Pill DS', 'Levonorgestrel', '1.5 mg', 'Tablet', 'Popular Pharmaceuticals PLC'],
                ['I-POP', 'Domperidone Maleate', '10 mg', 'Tablet', 'Doctor TIMS Pharmaceuticals Ltd.'],
                ['I-Sol', 'Sodium Chloride', '5%', 'Other', 'OSL Pharma Limited'],
                ['I-Ver', 'Ivermectin', '6 mg', 'Tablet', 'Globe Pharmaceuticals Ltd.'],
                ['I-Vita', 'Vitamin C + Vitamin E + Lutein + Copper + Zinc', 'Eye supplement', 'Other', 'Opsonin Pharma Ltd.'],
                ['Ibakin', 'Imatinib Mesylate', '100 mg', 'Tablet', 'Genvio Pharma Ltd.'],
                ['Ibakin', 'Imatinib Mesylate', '400 mg', 'Tablet', 'Genvio Pharma Ltd.'],
                ['Ibandron', 'Ibandronic Acid', '150 mg', 'Tablet', 'Aristopharma Ltd.'],
                ['Iben', 'Ibuprofen', '400 mg', 'Tablet', 'Zenith Pharmaceuticals Ltd.'],
                ['IBF', 'Ibuprofen', '400 mg', 'Tablet', 'Decent Pharma Laboratories Ltd.'],
                ['Ibida', 'Rifaximin', '200 mg', 'Tablet', 'Healthcare Pharmaceuticals Ltd.'],
                ['Ibida', 'Rifaximin', '550 mg', 'Tablet', 'Healthcare Pharmaceuticals Ltd.'],
                ['Ibnsina Tasty Saline', 'ORS', 'Sachet', 'Sachet', 'The IBN SINA Pharmaceutical PLC'],
            ],
            'J' => [
                ['J-Zinc', 'Zinc Sulfate Monohydrate', '10 mg/5 ml', 'Syrup', 'Ad-din Pharmaceuticals Ltd.'],
                ['Jadenu', 'Deferasirox', '90 mg', 'Tablet', 'Nevian Lifescience PLC'],
                ['Jadenu', 'Deferasirox', '360 mg', 'Tablet', 'Nevian Lifescience PLC'],
                ['Jafa', 'Ondansetron', '8 mg', 'Tablet', 'Doctor TIMS Pharmaceuticals Ltd.'],
                ['Jakavi', 'Ruxolitinib', '5 mg', 'Tablet', 'Nevian Lifescience PLC'],
                ['Jakloc', 'Tofacitinib', '5 mg', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['Jakloc XR', 'Tofacitinib', '11 mg XR', 'Tablet', 'Popular Pharmaceuticals Ltd.'],
                ['Jaknib', 'Tofacitinib', '5 mg', 'Tablet', 'Healthcare Pharmaceuticals Ltd.'],
                ['Jakrif', 'Tofacitinib', '5 mg', 'Tablet', 'Renata PLC'],
                ['Jaktor', 'Tofacitinib', '5 mg', 'Tablet', 'Beacon Pharmaceuticals PLC'],
                ['Jaktor XR', 'Tofacitinib', '11 mg XR', 'Tablet', 'Beacon Pharmaceuticals PLC'],
                ['Janmet', 'Sitagliptin + Metformin', '50 mg + 500 mg', 'Tablet', 'ACME Laboratories Ltd.'],
                ['Janmet', 'Sitagliptin + Metformin', '50 mg + 1000 mg', 'Tablet', 'ACME Laboratories Ltd.'],
                ['Janmet XR', 'Sitagliptin + Metformin', '50 mg + 500 mg XR', 'Tablet', 'ACME Laboratories Ltd.'],
                ['Janvia', 'Sitagliptin', '50 mg', 'Tablet', 'ACME Laboratories Ltd.'],
                ['Janvia', 'Sitagliptin', '100 mg', 'Tablet', 'ACME Laboratories Ltd.'],
                ['Japime', 'Cefepime Hydrochloride', '1 gm/vial', 'Injection', 'Jayson Pharmaceutical Ltd.'],
                ['Jardian', 'Empagliflozin', '10 mg', 'Tablet', 'Beximco Pharmaceuticals Ltd.'],
                ['Jardian', 'Empagliflozin', '25 mg', 'Tablet', 'Beximco Pharmaceuticals Ltd.'],
                ['Jardiance', 'Empagliflozin', '10 mg', 'Tablet', 'Radiant Pharmaceuticals Ltd.'],
                ['Jardiance', 'Empagliflozin', '25 mg', 'Tablet', 'Radiant Pharmaceuticals Ltd.'],
                ['Jardimet', 'Empagliflozin + Metformin', '5 mg + 500 mg', 'Tablet', 'Beximco Pharmaceuticals Ltd.'],
                ['Jardimet XR', 'Empagliflozin + Metformin', '5 mg + 1000 mg XR', 'Tablet', 'Beximco Pharmaceuticals Ltd.'],
                ['Jasocaine', 'Lidocaine Hydrochloride', '2%', 'Jelly', 'Jayson Pharmaceutical Ltd.'],
                ['Jasocaine', 'Lidocaine Hydrochloride', '2%', 'Injection', 'Jayson Pharmaceutical Ltd.'],
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
            str_contains($form, 'injection'), str_contains($form, 'vial') => 'injection',
            str_contains($form, 'suspension') => 'suspension',
            str_contains($form, 'syrup'), str_contains($form, 'solution') => 'syrup',
            str_contains($form, 'drop') => 'drops',
            str_contains($form, 'spray') => 'inhaler',
            str_contains($form, 'cream'), str_contains($form, 'topical'), str_contains($form, 'lotion') => 'cream',
            str_contains($form, 'jelly') => 'gel',
            str_contains($form, 'sachet') => 'sachet',
            str_contains($form, 'capsule') => 'capsule',
            str_contains($form, 'other') => 'other',
            default => 'tablet',
        };
    }
}
