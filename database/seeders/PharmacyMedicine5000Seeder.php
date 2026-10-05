<?php

namespace Database\Seeders;

use App\Models\GenericName;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyMedicineListASeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $rows = preg_split('/\R/', trim(
                <<<'DATA'
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
5-IN-1 Cleanser	Miscellaneous Topical Agents	N/A	Cream	DBL Healthcare Ltd.
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
A-Fenac SR	Diclofenac Sodium	100 mg SR	Tablet	ACME Laboratories Ltd.
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
A-One XR	Paracetamol	665 mg XR	Tablet	Apex Pharma Ltd.
A-Pak	Aceclofenac	100 mg	Tablet	Benham Pharmaceuticals Ltd.
A-Pak SR	Aceclofenac	200 mg SR	Tablet	Benham Pharmaceuticals Ltd.
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
DATA
            ));

            foreach ($rows as $index => $row) {

                $row = trim($row);

                if ($row === '') {
                    continue;
                }

                /*
                 * IMPORTANT:
                 * Your source data should contain TAB characters
                 * between the five columns.
                 */
                $columns = preg_split('/\t+/', $row);

                if (count($columns) !== 5) {
                    $this->command?->warn(
                        "Skipping row " . ($index + 1) . ": unable to parse -> {$row}"
                    );

                    continue;
                }

                [
                    $brand,
                    $genericName,
                    $strength,
                    $form,
                    $supplierName
                ] = array_map('trim', $columns);

                /*
                 |--------------------------------------------------------------------------
                 | Generic
                 |--------------------------------------------------------------------------
                 */
                $generic = GenericName::firstOrCreate(
                    [
                        'name' => $genericName,
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                /*
                 |--------------------------------------------------------------------------
                 | Brand
                 |--------------------------------------------------------------------------
                 */
                $brandRecord = Brand::firstOrCreate(
                    [
                        'name' => $brand,
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                /*
                 |--------------------------------------------------------------------------
                 | Supplier
                 |--------------------------------------------------------------------------
                 */
                $supplier = Supplier::firstOrCreate(
                    [
                        'name' => $supplierName,
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                /*
                 |--------------------------------------------------------------------------
                 | Category
                 |--------------------------------------------------------------------------
                 */
                $category = $this->categoryFor($form);

                /*
                 |--------------------------------------------------------------------------
                 | Unit
                 |--------------------------------------------------------------------------
                 */
                $unit = $this->unitFor($category);

                /*
                 |--------------------------------------------------------------------------
                 | Pieces per strip
                 |--------------------------------------------------------------------------
                 */
                $piecesPerStrip = in_array(
                    $category,
                    ['tablet', 'capsule'],
                    true
                )
                    ? 10
                    : 1;

                /*
                 |--------------------------------------------------------------------------
                 | Product Name
                 |--------------------------------------------------------------------------
                 */
                $productName = trim(
                    "{$brand} {$strength} {$form}"
                );

                /*
                 |--------------------------------------------------------------------------
                 | Product
                 |--------------------------------------------------------------------------
                 */
                $product = Product::updateOrCreate(
                    [
                        'name' => $productName,
                        'generic_name_id' => $generic->id,
                    ],
                    [
                        'barcode' => 'LSTA' . str_pad(
                            (string) ($index + 1),
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),

                        'brand_id' => $brandRecord->id,

                        'default_supplier_id' => $supplier->id,

                        'category' => $category,

                        'unit' => $unit,

                        'pieces_per_strip' => $piecesPerStrip,

                        'sell_by_piece' => true,

                        'sell_by_strip' => in_array(
                            $category,
                            ['tablet', 'capsule'],
                            true
                        ),

                        'reorder_level' => 10,

                        'is_active' => true,
                    ]
                );

                /*
                 |--------------------------------------------------------------------------
                 | Batch
                 |--------------------------------------------------------------------------
                 |
                 | Every product gets stock.
                 |
                 | quantity_received = 100
                 | quantity_available = 100
                 |
                 | Expiry is 2 years from today, therefore the POS
                 | catalog will include this batch.
                 |
                 */
                $batchNumber = 'LSTA-B' . str_pad(
                    (string) ($index + 1),
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                /*
                 * Demo/default prices.
                 *
                 * You can later replace these with your real
                 * purchase/sale prices.
                 */
                $purchasePrice = $this->purchasePriceFor($category);

                $salePrice = $this->salePriceFor(
                    $category,
                    $purchasePrice
                );

                $stripSalePrice = in_array(
                    $category,
                    ['tablet', 'capsule'],
                    true
                )
                    ? $salePrice * $piecesPerStrip
                    : null;

                /*
                 |--------------------------------------------------------------------------
                 | Create / Update Batch
                 |--------------------------------------------------------------------------
                 */
                ProductBatch::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                    ],
                    [
                        'supplier_id' => $supplier->id,

                        'quantity_received' => 100,

                        'quantity_available' => 100,

                        'purchase_price' => $purchasePrice,

                        'sale_price' => $salePrice,

                        'strip_sale_price' => $stripSalePrice,

                        'expires_on' => now()
                            ->addYears(2)
                            ->toDateString(),
                    ]
                );

                $this->command?->info(
                    "Imported: {$productName} | Batch: {$batchNumber}"
                );
            }
        });
    }

    /**
     * Determine product category from dosage form.
     */
    private function categoryFor(string $form): string
    {
        $form = strtolower(trim($form));

        return match (true) {

            str_contains($form, 'injection')
            => 'injection',

            str_contains($form, 'suspension')
            => 'suspension',

            str_contains($form, 'syrup')
            => 'syrup',

            str_contains($form, 'drop')
            => 'drops',

            str_contains($form, 'cream')
            => 'cream',

            str_contains($form, 'gel')
            => 'gel',

            str_contains($form, 'lotion')
            => 'lotion',

            str_contains($form, 'suppository')
            => 'suppository',

            str_contains($form, 'capsule')
            => 'capsule',

            default
            => 'tablet',
        };
    }

    /**
     * Determine stock unit.
     */
    private function unitFor(string $category): string
    {
        return match ($category) {

            'suspension',
            'syrup',
            'drops',
            'cream',
            'gel',
            'lotion'
            => 'bottle',

            'injection'
            => 'vial',

            'suppository',
            'tablet',
            'capsule'
            => 'piece',

            default
            => 'piece',
        };
    }

    /**
     * Default purchase price for demo/seeding.
     *
     * Change these later when importing real pricing.
     */
    private function purchasePriceFor(string $category): float
    {
        return match ($category) {

            'injection'
            => 80.00,

            'syrup',
            'suspension'
            => 50.00,

            'drops'
            => 40.00,

            'cream',
            'gel',
            'lotion'
            => 60.00,

            'capsule'
            => 5.00,

            'tablet'
            => 3.00,

            'suppository'
            => 10.00,

            default
            => 5.00,
        };
    }

    /**
     * Default selling price.
     */
    private function salePriceFor(
        string $category,
        float $purchasePrice
    ): float {
        /*
         * 20% markup.
         */
        return round($purchasePrice * 1.20, 2);
    }
}
