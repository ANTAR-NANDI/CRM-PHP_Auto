<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PharmacyMedicineListASeeder extends Seeder
{
    public function run(): void
    {
        $rows = preg_split('/\R/', trim(<<<'DATA'
3 Bion	Vitamin B1 + Vitamin B6 + Vitamin B12	100 mg + 200 mg + 200 mcg	Tablet	Jenphar Bangladesh Ltd.
3-C	Cefixime Trihydrate	200 mg	Capsule/Tablet	Edruc Limited
3-C	Cefixime Trihydrate	100 mg/5 ml	Suspension	Edruc Limited
3-C	Cefixime Trihydrate	400 mg	Capsule/Tablet	Edruc Limited
3-F	Levofloxacin Hemihydrate	500 mg	Tablet	Edruc Limited
3-Geocef	Cefixime Trihydrate	200 mg	Capsule/Tablet	Hallmark Pharmaceuticals Ltd.
3-Geocef	Cefixime Trihydrate	100 mg/5 ml	Suspension	Hallmark Pharmaceuticals Ltd.
3D	Cholecalciferol	20000 IU	Capsule/Tablet	Jenphar Bangladesh Ltd.
3D	Cholecalciferol	40000 IU	Capsule/Tablet	Jenphar Bangladesh Ltd.
3D	Cholecalciferol	2000 IU	Capsule/Tablet	Jenphar Bangladesh Ltd.
3RD Cef	Cefixime Trihydrate	200 mg	Capsule/Tablet	Medimet Pharmaceuticals Ltd.
3RD Cef	Cefixime Trihydrate	400 mg	Capsule/Tablet	Medimet Pharmaceuticals Ltd.
3RD Cef	Cefixime Trihydrate	100 mg/5 ml	Suspension	Medimet Pharmaceuticals Ltd.
5 FU PhaRes	Fluorouracil	25 mg/ml	Injection	ZAS Corporation
5-Fluril	Fluorouracil	25 mg/ml	Injection	Techno Drugs Ltd.
5-IN-1 Cleanser Cream	Miscellaneous Topical Agents	N/A	Cream	DBL Healthcare Ltd.
5X	Ulipristal Acetate	30 mg	Tablet	Renata PLC
A to Z Silver	Multivitamin + Multimineral	N/A	Tablet	Pharmik Laboratories Ltd.
A-B1	Thiamine Hydrochloride	100 mg	Tablet	ACME Laboratories Ltd.
A-Cal	Calcium Carbonate	500 mg	Tablet	ACME Laboratories Ltd.
A-Cal D	Calcium + Vitamin D3	500 mg + 200 IU	Tablet	ACME Laboratories Ltd.
A-Cal DX	Calcium + Vitamin D3	500 mg + 400 IU	Tablet	ACME Laboratories Ltd.
A-Calm	Tolperisone Hydrochloride	50 mg	Tablet	ACME Laboratories Ltd.
A-Card	Isosorbide Mononitrate	20 mg	Tablet	ACME Laboratories Ltd.
A-Care	Beta Carotene + Vitamin C + Vitamin E	6 mg + 200 mg + 50 mg	Tablet	Asiatic Laboratories Ltd.
A-Clox	Cloxacillin Sodium	500 mg	Capsule	ACME Laboratories Ltd.
A-Cof	Dextromethorphan + Pseudoephedrine + Triprolidine	10 mg + 30 mg + 1.25 mg/5 ml	Syrup	ACME Laboratories Ltd.
A-Cold	Bromhexine Hydrochloride	4 mg/5 ml	Syrup	ACME Laboratories Ltd.
A-Fenac	Diclofenac Sodium	50 mg	Tablet	ACME Laboratories Ltd.
A-Fenac	Diclofenac Sodium	12.5 mg	Suppository/Tablet	ACME Laboratories Ltd.
A-Fenac K	Diclofenac Potassium	50 mg	Tablet	ACME Laboratories Ltd.
A-Fenac Plus	Diclofenac Sodium + Lidocaine Hydrochloride	75 mg + 20 mg/2 ml	Injection	ACME Laboratories Ltd.
A-Fenac SR	Diclofenac Sodium	100 mg	SR Tablet	ACME Laboratories Ltd.
A-Flox	Flucloxacillin Sodium	250 mg	Capsule	ACME Laboratories Ltd.
A-Flox	Flucloxacillin Sodium	500 mg	Capsule	ACME Laboratories Ltd.
A-Flox	Flucloxacillin Sodium	125 mg/5 ml	Suspension	ACME Laboratories Ltd.
A-Flox	Flucloxacillin Sodium	500 mg/vial	Injection	ACME Laboratories Ltd.
A-Forte	Vitamin A	50000 IU	Capsule	Globe Pharmaceuticals Ltd.
A-Kit	Mifepristone + Misoprostol	200 mg + 200 mcg	Tablet Kit	ACME Laboratories Ltd.
A-Meb	Mebeverine Hydrochloride	135 mg	Tablet	ACME Laboratories Ltd.
A-Mectin	Ivermectin	6 mg	Tablet	ACME Laboratories Ltd.
A-Mectin	Ivermectin	12 mg	Tablet	ACME Laboratories Ltd.
A-Migel	Miconazole Nitrate	2% w/w	Gel/Cream	ACME Laboratories Ltd.
A-Mycin	Erythromycin	3% W/V	Lotion	Aristopharma Ltd.
A-Mycin	Erythromycin	125 mg/5 ml	Suspension	Aristopharma Ltd.
A-Mycin	Erythromycin	200 mg/5 ml	Suspension	Aristopharma Ltd.
A-One	Paracetamol	120 mg/5 ml	Suspension	Apex Pharma Ltd.
A-One Plus	Paracetamol + Caffeine	500 mg + 65 mg	Tablet	Apex Pharma Ltd.
A-One XR	Paracetamol	665 mg	XR Tablet	Apex Pharma Ltd.
A-Pak	Aceclofenac	100 mg	Tablet	Benham Pharmaceuticals Ltd.
A-Pak SR	Aceclofenac	200 mg	SR Tablet	Benham Pharmaceuticals Ltd.
A-Phenicol	Chloramphenicol	0.5%	Eye Drop	ACME Laboratories Ltd.
A-Phenicol D	Dexamethasone + Chloramphenicol	0.1% + 0.5%	Eye Drop	ACME Laboratories Ltd.
A-Rox	Roxithromycin	150 mg	Tablet	Ambee Pharmaceuticals Ltd.
A-Rox	Roxithromycin	300 mg	Tablet	Ambee Pharmaceuticals Ltd.
A-Rox	Roxithromycin	50 mg/5 ml	Suspension	Ambee Pharmaceuticals Ltd.
A-Spasm	Oxyphenonium Bromide	5 mg	Tablet	ACME Laboratories Ltd.
A-Statin	Atorvastatin Calcium	10 mg	Tablet	Doctor TIMS Pharmaceuticals Ltd.
A-Tetra	Tetracycline Hydrochloride	500 mg	Capsule	ACME Laboratories Ltd.
A-Zyme	Pancreatin	325 mg	Tablet	ACME Laboratories Ltd.
AB Kit	Mifepristone + Misoprostol	200 mg + 200 mcg	Tablet Kit	Renata PLC
AB-DS	Albendazole	400 mg	Tablet	Prime Pharmaceuticals Ltd.
Abac	Cephradine	500 mg	Capsule	Chemist Laboratories Ltd.
Abac	Cephradine	125 mg/5 ml	Suspension	Chemist Laboratories Ltd.
Abac	Cephradine	125 mg/1.25 ml	Paediatric Drop	Chemist Laboratories Ltd.
Abaclor	Cefaclor Monohydrate	500 mg	Capsule	ACI Limited
Abaclor	Cefaclor Monohydrate	125 mg/5 ml	Suspension	ACI Limited
Abaclor	Cefaclor Monohydrate	125 mg/1.25 ml	Paediatric Drop	ACI Limited
Abaclor	Cefaclor Monohydrate	250 mg	Capsule	ACI Limited
Abacten	Azithromycin Dihydrate	500 mg	Tablet	Arges Life Science Limited
Abacten	Azithromycin Dihydrate	200 mg/5 ml	Suspension	Arges Life Science Limited
Abasaglar	Insulin Glargine	100 IU/ml	Injection	Healthcare Pharmaceuticals Ltd.
ABC	Beta Carotene + Vitamin C + Vitamin E	6 mg + 200 mg + 50 mg	Tablet	Union Pharmaceuticals Ltd.
Abdolax	Sodium Picosulfate	10 mg	Tablet	Incepta Pharmaceuticals Ltd.
Abdolax	Sodium Picosulfate	5 mg/5 ml	Syrup	Incepta Pharmaceuticals Ltd.
Abdolax Max	Sodium Picosulfate	7.5 mg/ml	Oral Drop	Incepta Pharmaceuticals Ltd.
Abdorin	Dicycloverine Hydrochloride	10 mg	Tablet	Opsonin Pharma Ltd.
Abdorin	Dicycloverine Hydrochloride	10 mg/5 ml	Syrup	Opsonin Pharma Ltd.
Abecab	Amlodipine + Olmesartan	5 mg + 20 mg	Tablet	ACI Limited
Abecab	Amlodipine + Olmesartan	5 mg + 40 mg	Tablet	ACI Limited
Abecab	Amlodipine + Olmesartan	10 mg + 20 mg	Tablet	ACI Limited
Abecab	Amlodipine + Olmesartan	10 mg + 40 mg	Tablet	ACI Limited
Abeclib	Abemaciclib	200 mg	Tablet	Eskayef Pharmaceuticals Ltd.
Abeclib	Abemaciclib	150 mg	Tablet	Eskayef Pharmaceuticals Ltd.
Aben-DS	Albendazole	400 mg	Tablet	Team Pharmaceuticals Ltd.
Abetis	Olmesartan Medoxomil	10 mg	Tablet	ACI Limited
Abetis	Olmesartan Medoxomil	20 mg	Tablet	ACI Limited
Abetis	Olmesartan Medoxomil	40 mg	Tablet	ACI Limited
Abetis Plus	Olmesartan + Hydrochlorothiazide	20 mg + 12.5 mg	Tablet	ACI Limited
Abetis Plus	Olmesartan + Hydrochlorothiazide	40 mg + 12.5 mg	Tablet	ACI Limited
Abevmy	Bevacizumab	100 mg/4 ml	Injection	Beximco Pharmaceuticals Ltd.
Abevmy	Bevacizumab	400 mg/16 ml	Injection	Beximco Pharmaceuticals Ltd.
Abex	Pseudoephedrine + Guaifenesin + Triprolidine	30 mg + 100 mg + 1.25 mg/5 ml	Syrup	The IBN SINA Pharmaceutical PLC
Abex Plus	Guaifenesin + Levomenthol + Diphenhydramine	100 mg + 1.1 mg + 14 mg/5 ml	Syrup	The IBN SINA Pharmaceutical PLC
Abexol	Guaifenesin + Dextromethorphan + Menthol	200 mg + 15 mg + 15 mg/5 ml	Syrup	The IBN SINA Pharmaceutical PLC
Abeza	Abemaciclib	150 mg	Tablet	Beacon Pharmaceuticals PLC
Abeza	Abemaciclib	200 mg	Tablet	Beacon Pharmaceuticals PLC
Abezino	Abemaciclib	150 mg	Tablet	Healthcare Pharmaceuticals Ltd.
Abezino	Abemaciclib	200 mg	Tablet	Healthcare Pharmaceuticals Ltd.
DATA));

        foreach ($rows as $index => $row) {
            [$brand, $genericName, $strength, $form, $supplierName] = array_map('trim', explode("\t", $row));
            $generic = GenericName::firstOrCreate(['name' => $genericName], ['is_active' => true]);
            $brandRecord = Brand::firstOrCreate(['name' => $brand], ['is_active' => true]);
            $supplier = Supplier::firstOrCreate(['name' => $supplierName], ['is_active' => true]);
            $category = $this->categoryFor($form);
            Product::updateOrCreate(['name' => "{$brand} {$strength} {$form}", 'generic_name_id' => $generic->id], ['barcode' => 'LSTA'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT), 'brand_id' => $brandRecord->id, 'default_supplier_id' => $supplier->id, 'category' => $category, 'unit' => in_array($category, ['suspension', 'syrup'], true) ? 'bottle' : 'piece', 'pieces_per_strip' => in_array($category, ['tablet', 'capsule'], true) ? 10 : 1, 'sell_by_piece' => true, 'sell_by_strip' => in_array($category, ['tablet', 'capsule'], true), 'reorder_level' => 10, 'is_active' => true]);
        }
    }

    private function categoryFor(string $form): string
    {
        $form = strtolower($form);
        return match (true) {
            str_contains($form, 'injection') => 'injection', str_contains($form, 'suspension') => 'suspension', str_contains($form, 'syrup') => 'syrup', str_contains($form, 'drop') => 'drops', str_contains($form, 'cream') => 'cream', str_contains($form, 'gel') => 'gel', str_contains($form, 'suppository') => 'suppository', str_contains($form, 'capsule') => 'capsule', default => 'tablet',
        };
    }
}
