<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineRealQtoVSeeder extends Seeder
{
    public function run(): void
    {
        $catalogues = [
            'Q' => "Q Ben DS|Albendazole|400 mg|Concord Pharmaceuticals Ltd.\nQ-Fit|Quetiapine|25 mg|Everest Pharmaceuticals Ltd.\nQ-Fit|Quetiapine|100 mg|Everest Pharmaceuticals Ltd.\nQ-Fit XR|Quetiapine|50 mg XR|Everest Pharmaceuticals Ltd.\nQ-Rash|Zinc Oxide|40%|Beximco Pharmaceuticals Ltd.\nQcet|Paracetamol|500 mg|OSL Pharma Limited\nQcet|Paracetamol|120 mg/5 ml|OSL Pharma Limited\nQcet|Paracetamol|80 mg/ml|OSL Pharma Limited\nQcet Cafe|Paracetamol + Caffeine|500 mg + 65 mg|OSL Pharma Limited\nQcet XR|Paracetamol|665 mg|OSL Pharma Limited\nQcin|Clindamycin|150 mg|Renata PLC\nQcin|Clindamycin|300 mg|Renata PLC\nQmax|Quetiapine|25 mg|ACI Limited\nQmax|Quetiapine|100 mg|ACI Limited\nQmax XR|Quetiapine|200 mg|ACI Limited\nQmax XR|Quetiapine|300 mg|ACI Limited\nQNOL|Ciprofloxacin|500 mg|Decent Pharma Laboratories Ltd.\nQpine|Quetiapine|25 mg|Synovia Pharma PLC\nQpine|Quetiapine|100 mg|Synovia Pharma PLC\nQpine|Quetiapine|200 mg|Synovia Pharma PLC\nQpine XR|Quetiapine|50 mg|Synovia Pharma PLC\nQpine XR|Quetiapine|200 mg|Synovia Pharma PLC\nQrip|Aceclofenac|100 mg|Synovia Pharma PLC\nQtia|Quetiapine|25 mg|Naafco Pharma PLC\nQtia|Quetiapine|100 mg|Naafco Pharma PLC",
            'R' => "R-20|Rabeprazole|20 mg|Asiatic Laboratories Ltd.\nR-FU|Fluorouracil|25 mg/ml Injection|Renata PLC\nR-Lix|Relugolix|120 mg|Eskayef Pharmaceuticals Ltd.\nR-Pag|Eltrombopag|12.5 mg|Renata PLC\nR-Pag|Eltrombopag|25 mg|Renata PLC\nR-Pag|Eltrombopag|50 mg|Renata PLC\nR-Penem|Meropenem|500 mg/vial|Jenphar Bangladesh Ltd.\nR-Penem|Meropenem|1 gm/vial|Jenphar Bangladesh Ltd.\nR-Pil|Ramipril|1.25 mg|Biopharma Limited\nR-Pil|Ramipril|2.5 mg|Biopharma Limited\nR-Pil|Ramipril|5 mg|Biopharma Limited\nR-Proton|Rabeprazole|20 mg|Doctor TIMS Pharmaceuticals Ltd.\nR-Saline-N|ORS|10.25 gm Sachet|Rephco Pharmaceuticals Ltd.\nR-Vtin|Rosuvastatin|10 mg|Nipa Pharmaceuticals Ltd.\nR-Zol|Albendazole|400 mg|Reman Drug Laboratories Ltd.\nRabe|Rabeprazole|20 mg|Aristopharma Ltd.\nRabeca|Rabeprazole|20 mg|Square Pharmaceuticals PLC\nRabecon|Rabeprazole|20 mg|Biopharma Limited\nRabefour|Rabeprazole|20 mg|Albion Laboratories Limited\nRabegend|Rabeprazole|20 mg|Apex Pharma Ltd.\nRabemax|Rabeprazole|20 mg|General Pharmaceuticals Ltd.\nRabenaaf|Rabeprazole|20 mg|Naafco Pharma PLC\nRabenta|Rabeprazole|20 mg|DBL Pharmaceuticals Ltd.\nRabepes|Rabeprazole|20 mg|Beacon Pharmaceuticals PLC\nRabepes MUPS|Rabeprazole|20 mg|Beacon Pharmaceuticals PLC",
            'S' => "S-32 Gold|Multivitamin + Multimineral|Tablet|Sharif Pharmaceuticals Ltd.\nS-Citapram|Escitalopram|5 mg|General Pharmaceuticals Ltd.\nS-Citapram|Escitalopram|10 mg|General Pharmaceuticals Ltd.\nS-Fenac TR|Diclofenac|100 mg|Seema Pharmaceuticals Ltd.\nS-Fenac TR|Diclofenac|50 mg|Seema Pharmaceuticals Ltd.\nS-Fer|Sodium Feredetate|27.5 mg/5 ml|Navana Pharmaceuticals Ltd.\nS-Fluclox|Flucloxacillin|250 mg|Seema Pharmaceuticals Ltd.\nS-Fluclox|Flucloxacillin|500 mg|Seema Pharmaceuticals Ltd.\nS-Kinase|Streptokinase|1.5 million IU|Popular Pharmaceuticals Ltd.\nS-Kit|Ketotifen|1 mg|Sharif Pharmaceuticals Ltd.\nS-Kit|Ketotifen|1 mg/5 ml|Sharif Pharmaceuticals Ltd.\nS-Ome|Esomeprazole|20 mg|Somatec Pharmaceuticals Ltd.\nS-Ome|Esomeprazole|40 mg|Somatec Pharmaceuticals Ltd.\nS-Pirin|Aspirin|100 mg|Navana Pharmaceuticals Ltd.\nS-Vom|Meclizine + Pyridoxine|25 mg + 50 mg|Sharif Pharmaceuticals Ltd.\nSabicard|Sacubitril + Valsartan|24 mg + 26 mg|Aristopharma Ltd.\nSabicard|Sacubitril + Valsartan|49 mg + 51 mg|Aristopharma Ltd.\nSabicard|Sacubitril + Valsartan|97 mg + 103 mg|Aristopharma Ltd.\nSabitar|Sacubitril + Valsartan|24 mg + 26 mg|Incepta Pharmaceuticals Ltd.\nSabitar|Sacubitril + Valsartan|49 mg + 51 mg|Incepta Pharmaceuticals Ltd.\nSabitar|Sacubitril + Valsartan|97 mg + 103 mg|Incepta Pharmaceuticals Ltd.\nSabul Plus|Salbutamol + Ipratropium|Nebulizer solution|Chemist Laboratories Ltd.\nSabutanol|Salbutamol|2 mg|Bristol Pharmaceuticals Ltd.\nSabutanol|Salbutamol|4 mg|Bristol Pharmaceuticals Ltd.\nSacona|Fluconazole|150 mg|Marker Pharma Ltd.",
            'T' => "T-Cef|Cefixime|200 mg|Drug International Ltd.\nT-Cef|Cefixime|400 mg|Drug International Ltd.\nT-Cef|Cefixime|100 mg/5 ml|Drug International Ltd.\nT-Cef DS|Cefixime|200 mg/5 ml|Drug International Ltd.\nT-Cort|Fluticasone|0.05%|Gaco Pharmaceuticals Ltd.\nT-cure|Naftifine|2%|Incepta Pharmaceuticals Ltd.\nT-Dex|Dexamethasone + Tobramycin|0.1% + 0.3% Eye Drop|Reman Drug Laboratories Ltd.\nT-Drop|Tobramycin|0.3% Eye Drop|Reman Drug Laboratories Ltd.\nT-Fovir|Tenofovir|300 mg|Drug International Ltd.\nT-Fovir A|Tenofovir Alafenamide|25 mg|Drug International Ltd.\nT-H|Thiamine|100 mg|Bristol Pharmaceuticals Ltd.\nT-Mycin|Tobramycin|0.3% Eye Drop|Aristopharma Ltd.\nT-Mycin Plus|Dexamethasone + Tobramycin|Eye Drop|Aristopharma Ltd.\nT-zol|Tinidazole|500 mg|Popular Pharmaceuticals Ltd.\nT-zol|Tinidazole|1 gm|Popular Pharmaceuticals Ltd.\nT4|Levothyroxine|25 mcg|Popular Pharmaceuticals Ltd.\nT4|Levothyroxine|50 mcg|Popular Pharmaceuticals Ltd.\nTabis|Bisoprolol|2.5 mg|Navana Pharmaceuticals Ltd.\nTabis|Bisoprolol|5 mg|Navana Pharmaceuticals Ltd.\nTabis Plus|Bisoprolol + HCTZ|2.5 mg + 6.25 mg|Navana Pharmaceuticals Ltd.\nTabrex|Azithromycin|500 mg|Aztec Pharmaceuticals Ltd.\nTaclimus|Tacrolimus|1 mg|Radiant Pharmaceuticals Ltd.\nTacrocap|Tacrolimus|0.5 mg|Healthcare Pharmaceuticals Ltd.\nTacrocap|Tacrolimus|1 mg|Healthcare Pharmaceuticals Ltd.\nTacroderm|Tacrolimus|0.1%|Popular Pharmaceuticals Ltd.",
            'U' => "U-Pepton|Pantoprazole|20 mg|Union Pharmaceuticals Ltd.\nU-Tovas|Atorvastatin|10 mg|Union Pharmaceuticals Ltd.\nU4|Flupentixol + Melitracen|0.5 mg + 10 mg|Orion Pharma Ltd.\nUAC|Aceclofenac|100 mg|Union Pharmaceuticals Ltd.\nUbilon|Tibolone|2.5 mg|Incepta Pharmaceuticals Ltd.\nUbro|Bromhexine|4 mg/5 ml|Union Pharmaceuticals Ltd.\nUcandi|Itraconazole|100 mg|Union Pharmaceuticals Ltd.\nUcard|Amlodipine|5 mg|Union Pharmaceuticals Ltd.\nUcardol|Carvedilol|6.25 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUcardol|Carvedilol|12.5 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUCEP|Cephalexin|250 mg|Union Pharmaceuticals Ltd.\nUcet|Paracetamol|250 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUcet|Paracetamol|500 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUcet Extend|Paracetamol|665 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUcet Extra|Paracetamol + Caffeine|500 mg + 65 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUcol|Tolterodine|2 mg|Square Pharmaceuticals PLC\nUcorex|Allopurinol|100 mg|Healthcare Pharmaceuticals Ltd.\nUcrafate|Sucralfate|1 gm/5 ml|UniMed UniHealth Pharmaceuticals Ltd.\nUcrafate|Sucralfate|1000 mg|UniMed UniHealth Pharmaceuticals Ltd.\nUDCA|Ursodeoxycholic Acid|300 mg|Biopharma Limited\nUdihep|UDCA|150 mg|Mundipharma\nUdihep Fort|UDCA|300 mg|Mundipharma\nUdiliva|UDCA|150 mg|Everest Pharmaceuticals Ltd.\nUdiliva|UDCA|300 mg|Everest Pharmaceuticals Ltd.\nUfenac|Aceclofenac|100 mg|UniMed UniHealth Pharmaceuticals Ltd.",
            'V' => "V One|Voriconazole|200 mg|Salton Pharmaceuticals Ltd.\nV-Cod|Multivitamin + Cod Liver Oil|Capsule|Edruc Limited\nV-gin|Tiemonium|50 mg|Pharmik Laboratories Ltd.\nV-Nerve|Vitamin B1 + B6 + B12|Tablet|Monicopharma Ltd.\nV-Nil|Meclizine + Pyridoxine|25 mg + 50 mg|Alco Pharma Ltd.\nV-Plex|Vitamin B Complex|Tablet|ACME Laboratories Ltd.\nV-Plex Plus|Multivitamin + Multimineral|Tablet|ACME Laboratories Ltd.\nV3N|Vitamin B1 + B6 + B12|Tablet|NIPRO JMI Pharma Ltd.\nValarux|Valacyclovir|500 mg|Opsonin Pharma Ltd.\nValarux|Valacyclovir|1 gm|Opsonin Pharma Ltd.\nValdipin|Amlodipine + Valsartan|5 mg + 80 mg|Renata PLC\nValdipin|Amlodipine + Valsartan|5 mg + 160 mg|Renata PLC\nValentino|Drospirenone + Ethinyl Estradiol|Tablet|Healthcare Pharmaceuticals Ltd.\nValenty|Vardenafil|10 mg|Eskayef Pharmaceuticals Ltd.\nValenty|Vardenafil|20 mg|Eskayef Pharmaceuticals Ltd.\nValepi|Sodium Valproate|200 mg|Medimet Pharmaceuticals Ltd.\nValepsy|Sodium Valproate|200 mg/5 ml|Healthcare Pharmaceuticals Ltd.\nValepsy CR|Sodium Valproate|200 mg|Healthcare Pharmaceuticals Ltd.\nVabysmo|Faricimab|6 mg/0.05 ml|Roche\nV Wash|Lactic Acid|1.2%|Diva’s Secret\nV-Care|Lactic Acid|1.2%|ZAS Corporation\nV4Z|Vitamin B Complex + Zinc|Tablet|NIPRO JMI Pharma Ltd.\nV-Plex Injection|Vitamin B Complex|Injection|ACME Laboratories Ltd.\nV-Plex Syrup|Vitamin B Complex|Syrup|ACME Laboratories Ltd.\nV-Nerve Injection|Vitamin B1 + B6 + B12|Injection|Monicopharma Ltd.",
        ];

        foreach ($catalogues as $letter => $rows) {
            foreach (explode("\n", $rows) as $index => $row) {
                [$brand, $genericName, $strength, $supplierName] = explode('|', $row);
                $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
                $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
                $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
                $category = $this->categoryFor($brand.' '.$strength);

                Product::updateOrCreate(
                    ['name' => trim("{$brand} {$strength}"), 'generic_name_id' => $generic->id],
                    ['barcode' => 'LST'.$letter.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT), 'brand_id' => $brandRecord->id, 'default_supplier_id' => $supplier->id, 'category' => $category, 'unit' => in_array($category, ['syrup', 'suspension', 'drops'], true) ? 'bottle' : 'piece', 'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1, 'sell_by_piece' => true, 'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true), 'reorder_level' => 10, 'is_active' => true]
                );
            }
        }
    }

    private function categoryFor(string $value): string
    {
        $value = strtolower($value);

        return match (true) {
            str_contains($value, 'injection'), str_contains($value, 'vial'), str_contains($value, 'mg/ml') => 'injection',
            str_contains($value, 'sachet') => 'sachet',
            str_contains($value, 'eye drop'), str_contains($value, 'drop') => 'drops',
            str_contains($value, 'syrup'), str_contains($value, 'mg/5 ml') => 'syrup',
            str_contains($value, 'nebulizer') => 'other',
            default => 'tablet',
        };
    }
}
