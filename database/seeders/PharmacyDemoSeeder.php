<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\GenericName;
use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PartyAccountService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PharmacyDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $employees = $this->seedEmployees();
            $suppliers = $this->seedSuppliers();
            $generics = $this->seedGenericNames();
            $brands = $this->seedBrands();

            $products = $this->seedProducts($generics, $brands);

            $this->seedCustomers();

            $this->seedPurchases(
                $products,
                $suppliers,
                $employees['admin']
            );
        });
    }

    /**
     * ---------------------------------------------------------
     * EMPLOYEES
     * ---------------------------------------------------------
     */
    private function seedEmployees(): array
    {
        $roles = Role::query()->get()->keyBy('name');

        $employees = [
            [
                'name' => 'System Admin',
                'email' => 'admin@pharmacy.test',
                'employee_code' => 'EMP-00001',
                'phone' => '01710000001',
                'designation' => 'Administrator',
                'joining_date' => now()->subYears(2)->toDateString(),
                'salary' => 45000,
                'role' => 'admin',
            ],
            [
                'name' => 'Store Manager',
                'email' => 'manager@pharmacy.test',
                'employee_code' => 'EMP-00002',
                'phone' => '01710000002',
                'designation' => 'Pharmacy Manager',
                'joining_date' => now()->subYears(1)->toDateString(),
                'salary' => 38000,
                'role' => 'manager',
            ],
            [
                'name' => 'Salesperson One',
                'email' => 'salesperson@pharmacy.test',
                'employee_code' => 'EMP-00003',
                'phone' => '01710000003',
                'designation' => 'Salesperson',
                'joining_date' => now()->subYear()->toDateString(),
                'salary' => 28000,
                'role' => 'salesperson',
            ],
            [
                'name' => 'Salesperson Two',
                'email' => 'salesperson2@pharmacy.test',
                'employee_code' => 'EMP-00004',
                'phone' => '01710000004',
                'designation' => 'Salesperson',
                'joining_date' => now()->subMonths(10)->toDateString(),
                'salary' => 27000,
                'role' => 'salesperson',
            ],
            [
                'name' => 'Salesperson Three',
                'email' => 'salesperson3@pharmacy.test',
                'employee_code' => 'EMP-00005',
                'phone' => '01710000005',
                'designation' => 'Salesperson',
                'joining_date' => now()->subMonths(7)->toDateString(),
                'salary' => 26000,
                'role' => 'salesperson',
            ],
            [
                'name' => 'Salesperson Four',
                'email' => 'salesperson4@pharmacy.test',
                'employee_code' => 'EMP-00006',
                'phone' => '01710000006',
                'designation' => 'Salesperson',
                'joining_date' => now()->subMonths(4)->toDateString(),
                'salary' => 25000,
                'role' => 'salesperson',
            ],
        ];

        $result = [];

        foreach ($employees as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...collect($data)->all(),
                    'address' => 'Dhaka, Bangladesh',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            if (isset($roles[$data['role']])) {
                $user->syncRoles([$roles[$data['role']]]);
            }

            app(PartyAccountService::class)->forEmployee($user);

            $result[$data['role']] ??= $user;
        }

        return $result;
    }

    /**
     * ---------------------------------------------------------
     * SUPPLIERS
     * ---------------------------------------------------------
     */
    private function seedSuppliers(): array
    {
        return collect([
            [
                'name' => 'Square Pharmaceuticals Ltd.',
                'phone' => '01713000001',
                'email' => 'orders@squarepharma.example',
                'address' => 'Pabna, Bangladesh',
            ],
            [
                'name' => 'Incepta Pharmaceuticals Ltd.',
                'phone' => '01713000002',
                'email' => 'orders@inceptapharma.example',
                'address' => 'Savar, Dhaka',
            ],
            [
                'name' => 'Beximco Pharma Ltd.',
                'phone' => '01713000003',
                'email' => 'orders@beximco.example',
                'address' => 'Tongi, Gazipur',
            ],
            [
                'name' => 'Renata Limited',
                'phone' => '01713000004',
                'email' => 'orders@renata.example',
                'address' => 'Mirpur, Dhaka',
            ],
            [
                'name' => 'Opsonin Pharma Ltd.',
                'phone' => '01713000005',
                'email' => 'orders@opsonin.example',
                'address' => 'Barishal, Bangladesh',
            ],
            [
                'name' => 'ACI Limited',
                'phone' => '01713000006',
                'email' => 'orders@aci.example',
                'address' => 'Dhaka, Bangladesh',
            ],
            [
                'name' => 'Aristopharma Ltd.',
                'phone' => '01713000007',
                'email' => 'orders@aristopharma.example',
                'address' => 'Tejgaon, Dhaka',
            ],
            [
                'name' => 'Healthcare Pharmaceuticals Ltd.',
                'phone' => '01713000008',
                'email' => 'orders@healthcare.example',
                'address' => 'Dhaka, Bangladesh',
            ],
        ])->mapWithKeys(
            fn(array $data) => [
                $data['name'] => Supplier::updateOrCreate(
                    ['name' => $data['name']],
                    [
                        ...$data,
                        'is_active' => true,
                    ]
                ),
            ]
        )->all();
    }

    /**
     * ---------------------------------------------------------
     * GENERIC NAMES
     * ---------------------------------------------------------
     */
    private function seedGenericNames(): array
    {
        $names = [
            'Paracetamol',
            'Omeprazole',
            'Azithromycin',
            'Cetirizine',
            'Metformin',
            'Amlodipine',
            'Esomeprazole',
            'Vitamin C',

            'Ibuprofen',
            'Diclofenac',
            'Naproxen',
            'Amoxicillin',
            'Ciprofloxacin',
            'Doxycycline',
            'Cefixime',
            'Cefuroxime',
            'Flucloxacillin',
            'Clarithromycin',

            'Montelukast',
            'Loratadine',
            'Fexofenadine',
            'Levocetirizine',

            'Domperidone',
            'Ondansetron',
            'Famotidine',
            'Rabeprazole',
            'Pantoprazole',

            'Losartan',
            'Telmisartan',
            'Atenolol',
            'Bisoprolol',
            'Enalapril',
            'Olmesartan',

            'Glimepiride',
            'Gliclazide',
            'Sitagliptin',
            'Empagliflozin',

            'Atorvastatin',
            'Rosuvastatin',
            'Aspirin',
            'Clopidogrel',

            'Calcium Carbonate',
            'Vitamin D3',
            'Vitamin B Complex',
            'Iron',
            'Folic Acid',
            'Zinc',

            'Salbutamol',
            'Budesonide',
            'Theophylline',
            'Ambroxol',
            'Bromhexine',
            'Dextromethorphan',

            'Mupirocin',
            'Clotrimazole',
            'Fluconazole',
            'Ketoconazole',

            'Hydrocortisone',
            'Betamethasone',

            'Chlorpheniramine',
            'Diphenhydramine',

            'Tamsulosin',
            'Finasteride',

            'Sildenafil',
            'Tadalafil',

            'Tramadol',
            'Ketorolac',

            'Dexamethasone',
            'Prednisolone',

            'Metronidazole',
            'Tinidazole',

            'Acyclovir',
            'Valacyclovir',

            'Pregabalin',
            'Gabapentin',

            'Sertraline',
            'Escitalopram',

            'Clonazepam',
            'Diazepam',

            'Ranitidine',
            'Sucralfate',

            'Loperamide',
            'Bisacodyl',

            'ORS',
            'Glucose',
        ];

        $generics = [];

        foreach ($names as $name) {
            $generics[$name] = GenericName::updateOrCreate(
                ['name' => $name],
                [
                    'is_active' => true,
                ]
            );
        }

        return $generics;
    }

    /**
     * ---------------------------------------------------------
     * BRANDS
     * ---------------------------------------------------------
     */
    private function seedBrands(): array
    {
        $brands = [
            'Napa',
            'Ace Plus',
            'Seclo',
            'Azithro',
            'Histacin',
            'Comet',
            'Amdocal',
            'Esoral',

            'Ceevit',
            'DP',
            'Voltaren',
            'Naprosyn',
            'Amoxil',
            'Ciprocin',
            'Doxy',
            'Cefix',
            'Cefurox',
            'Fluclox',

            'Montair',
            'Lorat',
            'Fexo',
            'Levocet',

            'Domper',
            'Onset',
            'Famotid',
            'Rabe',
            'Pantoloc',

            'Losar',
            'Telma',
            'Atenol',
            'Bisol',
            'Enap',
            'Olmetec',

            'Glimp',
            'Gliclaz',
            'Sitagil',
            'Jardiance',

            'Atorva',
            'Rosuva',
            'Ecosprin',
            'Clopid',

            'Calbo',
            'D-Care',
            'Becosules',
            'Ferrous',
            'Folic',
            'Zincovit',

            'Ventolin',
            'Budicort',
            'Theo',
            'Mucolyt',
            'Bisolvon',
            'Dexa',

            'Bactroban',
            'Canesten',
            'Flucan',
            'Nizoral',

            'Hydrocort',
            'Betnovate',

            'Piriton',
            'DPH',

            'Uromax',
            'Finast',

            'Viagra',
            'Cialis',

            'Tramal',
            'Ketorol',

            'DPred',

            'Flagyl',
            'Fasigyn',

            'Zovirax',
            'Valtrex',

            'Pregaba',
            'Neurontin',

            'Serlift',
            'Lexapro',

            'Rivotril',
            'Valium',

            'Rani',
            'Sucral',

            'Imodium',
            'Dulcolax',

            'Orsaline',
            'Glucose-D',
        ];

        $result = [];

        foreach ($brands as $name) {
            $result[$name] = Brand::updateOrCreate(
                ['name' => $name],
                [
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * ---------------------------------------------------------
     * PRODUCTS - 100+
     * ---------------------------------------------------------
     */
    private function seedProducts(
        array $generics,
        array $brands
    ): array {
        $products = [

            // -------------------------------------------------
            // PARACETAMOL
            // -------------------------------------------------
            [
                'name' => 'Napa 500 mg',
                'barcode' => '890100000001',
                'generic' => 'Paracetamol',
                'brand' => 'Napa',
                'pieces_per_strip' => 10,
                'reorder_level' => 50,
                'purchase_price' => 21,
                'sale_price' => 3,
                'strip_price' => 28,
            ],
            [
                'name' => 'Napa 250 mg',
                'barcode' => '890100000002',
                'generic' => 'Paracetamol',
                'brand' => 'Napa',
                'pieces_per_strip' => 10,
                'reorder_level' => 50,
                'purchase_price' => 12,
                'sale_price' => 2,
                'strip_price' => 18,
            ],
            [
                'name' => 'Napa 650 mg',
                'barcode' => '890100000003',
                'generic' => 'Paracetamol',
                'brand' => 'Napa',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 25,
                'sale_price' => 4,
                'strip_price' => 35,
            ],
            [
                'name' => 'Napa Extra',
                'barcode' => '890100000004',
                'generic' => 'Paracetamol',
                'brand' => 'Napa',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 45,
            ],
            [
                'name' => 'Napa Syrup',
                'barcode' => '890100000005',
                'generic' => 'Paracetamol',
                'brand' => 'Napa',
                'pieces_per_strip' => 1,
                'reorder_level' => 15,
                'purchase_price' => 25,
                'sale_price' => 35,
                'strip_price' => 35,
            ],
            [
                'name' => 'Ace 500 mg',
                'barcode' => '890100000006',
                'generic' => 'Paracetamol',
                'brand' => 'Ace Plus',
                'pieces_per_strip' => 10,
                'reorder_level' => 50,
                'purchase_price' => 22,
                'sale_price' => 3,
                'strip_price' => 30,
            ],
            [
                'name' => 'Ace Plus',
                'barcode' => '890100000007',
                'generic' => 'Paracetamol',
                'brand' => 'Ace Plus',
                'pieces_per_strip' => 10,
                'reorder_level' => 50,
                'purchase_price' => 29,
                'sale_price' => 4,
                'strip_price' => 38,
            ],

            // -------------------------------------------------
            // GASTRIC
            // -------------------------------------------------
            [
                'name' => 'Seclo 20 mg',
                'barcode' => '890100000008',
                'generic' => 'Omeprazole',
                'brand' => 'Seclo',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 72,
                'sale_price' => 9,
                'strip_price' => 95,
            ],
            [
                'name' => 'Seclo 40 mg',
                'barcode' => '890100000009',
                'generic' => 'Omeprazole',
                'brand' => 'Seclo',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 110,
                'sale_price' => 14,
                'strip_price' => 145,
            ],
            [
                'name' => 'Seclo 10 mg',
                'barcode' => '890100000010',
                'generic' => 'Omeprazole',
                'brand' => 'Seclo',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 55,
                'sale_price' => 7,
                'strip_price' => 75,
            ],
            [
                'name' => 'Esoral 20 mg',
                'barcode' => '890100000011',
                'generic' => 'Esomeprazole',
                'brand' => 'Esoral',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 78,
                'sale_price' => 10,
                'strip_price' => 105,
            ],
            [
                'name' => 'Esoral 40 mg',
                'barcode' => '890100000012',
                'generic' => 'Esomeprazole',
                'brand' => 'Esoral',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 120,
                'sale_price' => 15,
                'strip_price' => 155,
            ],
            [
                'name' => 'Rabe 20 mg',
                'barcode' => '890100000013',
                'generic' => 'Rabeprazole',
                'brand' => 'Rabe',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 80,
                'sale_price' => 11,
                'strip_price' => 110,
            ],
            [
                'name' => 'Pantoloc 40 mg',
                'barcode' => '890100000014',
                'generic' => 'Pantoprazole',
                'brand' => 'Pantoloc',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 90,
                'sale_price' => 12,
                'strip_price' => 125,
            ],
            [
                'name' => 'Famotid 20 mg',
                'barcode' => '890100000015',
                'generic' => 'Famotidine',
                'brand' => 'Famotid',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 55,
            ],

            // -------------------------------------------------
            // ANTIBIOTICS
            // -------------------------------------------------
            [
                'name' => 'Azithro 500 mg',
                'barcode' => '890100000016',
                'generic' => 'Azithromycin',
                'brand' => 'Azithro',
                'pieces_per_strip' => 6,
                'reorder_level' => 24,
                'purchase_price' => 170,
                'sale_price' => 32,
                'strip_price' => 195,
            ],
            [
                'name' => 'Azithro 250 mg',
                'barcode' => '890100000017',
                'generic' => 'Azithromycin',
                'brand' => 'Azithro',
                'pieces_per_strip' => 6,
                'reorder_level' => 24,
                'purchase_price' => 100,
                'sale_price' => 20,
                'strip_price' => 120,
            ],
            [
                'name' => 'Azithro Suspension',
                'barcode' => '890100000018',
                'generic' => 'Azithromycin',
                'brand' => 'Azithro',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 90,
                'sale_price' => 120,
                'strip_price' => 120,
            ],
            [
                'name' => 'Amoxil 500 mg',
                'barcode' => '890100000019',
                'generic' => 'Amoxicillin',
                'brand' => 'Amoxil',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 75,
                'sale_price' => 11,
                'strip_price' => 120,
            ],
            [
                'name' => 'Amoxil 250 mg',
                'barcode' => '890100000020',
                'generic' => 'Amoxicillin',
                'brand' => 'Amoxil',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 50,
                'sale_price' => 8,
                'strip_price' => 85,
            ],
            [
                'name' => 'Ciprocin 500 mg',
                'barcode' => '890100000021',
                'generic' => 'Ciprofloxacin',
                'brand' => 'Ciprocin',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 80,
                'sale_price' => 12,
                'strip_price' => 125,
            ],
            [
                'name' => 'Doxy 100 mg',
                'barcode' => '890100000022',
                'generic' => 'Doxycycline',
                'brand' => 'Doxy',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 60,
                'sale_price' => 9,
                'strip_price' => 95,
            ],
            [
                'name' => 'Cefix 200 mg',
                'barcode' => '890100000023',
                'generic' => 'Cefixime',
                'brand' => 'Cefix',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 180,
                'sale_price' => 28,
                'strip_price' => 220,
            ],
            [
                'name' => 'Cefix 400 mg',
                'barcode' => '890100000024',
                'generic' => 'Cefixime',
                'brand' => 'Cefix',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 300,
                'sale_price' => 45,
                'strip_price' => 360,
            ],
            [
                'name' => 'Cefurox 250 mg',
                'barcode' => '890100000025',
                'generic' => 'Cefuroxime',
                'brand' => 'Cefurox',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 220,
                'sale_price' => 34,
                'strip_price' => 280,
            ],
            [
                'name' => 'Fluclox 500 mg',
                'barcode' => '890100000026',
                'generic' => 'Flucloxacillin',
                'brand' => 'Fluclox',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 130,
            ],

            // -------------------------------------------------
            // ALLERGY
            // -------------------------------------------------
            [
                'name' => 'Histacin 10 mg',
                'barcode' => '890100000027',
                'generic' => 'Cetirizine',
                'brand' => 'Histacin',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 24,
                'sale_price' => 3,
                'strip_price' => 32,
            ],
            [
                'name' => 'Histacin 5 mg',
                'barcode' => '890100000028',
                'generic' => 'Cetirizine',
                'brand' => 'Histacin',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 20,
                'sale_price' => 3,
                'strip_price' => 28,
            ],
            [
                'name' => 'Histacin Syrup',
                'barcode' => '890100000029',
                'generic' => 'Cetirizine',
                'brand' => 'Histacin',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 30,
                'sale_price' => 45,
                'strip_price' => 45,
            ],
            [
                'name' => 'Montair 10 mg',
                'barcode' => '890100000030',
                'generic' => 'Montelukast',
                'brand' => 'Montair',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 130,
            ],
            [
                'name' => 'Lorat 10 mg',
                'barcode' => '890100000031',
                'generic' => 'Loratadine',
                'brand' => 'Lorat',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Fexo 120 mg',
                'barcode' => '890100000032',
                'generic' => 'Fexofenadine',
                'brand' => 'Fexo',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 70,
                'sale_price' => 10,
                'strip_price' => 90,
            ],
            [
                'name' => 'Fexo 180 mg',
                'barcode' => '890100000033',
                'generic' => 'Fexofenadine',
                'brand' => 'Fexo',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 90,
                'sale_price' => 13,
                'strip_price' => 115,
            ],
            [
                'name' => 'Levocet 5 mg',
                'barcode' => '890100000034',
                'generic' => 'Levocetirizine',
                'brand' => 'Levocet',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],

            // -------------------------------------------------
            // DIABETES
            // -------------------------------------------------
            [
                'name' => 'Comet 500 mg',
                'barcode' => '890100000035',
                'generic' => 'Metformin',
                'brand' => 'Comet',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 18,
                'sale_price' => 2.5,
                'strip_price' => 25,
            ],
            [
                'name' => 'Comet 850 mg',
                'barcode' => '890100000036',
                'generic' => 'Metformin',
                'brand' => 'Comet',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 25,
                'sale_price' => 3.5,
                'strip_price' => 35,
            ],
            [
                'name' => 'Comet 1000 mg',
                'barcode' => '890100000037',
                'generic' => 'Metformin',
                'brand' => 'Comet',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Glimp 1 mg',
                'barcode' => '890100000038',
                'generic' => 'Glimepiride',
                'brand' => 'Glimp',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Glimp 2 mg',
                'barcode' => '890100000039',
                'generic' => 'Glimepiride',
                'brand' => 'Glimp',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 50,
                'sale_price' => 7,
                'strip_price' => 65,
            ],
            [
                'name' => 'Gliclaz 80 mg',
                'barcode' => '890100000040',
                'generic' => 'Gliclazide',
                'brand' => 'Gliclaz',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 60,
            ],
            [
                'name' => 'Sitagil 50 mg',
                'barcode' => '890100000041',
                'generic' => 'Sitagliptin',
                'brand' => 'Sitagil',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 300,
                'sale_price' => 45,
                'strip_price' => 380,
            ],
            [
                'name' => 'Jardiance 10 mg',
                'barcode' => '890100000042',
                'generic' => 'Empagliflozin',
                'brand' => 'Jardiance',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 350,
                'sale_price' => 50,
                'strip_price' => 420,
            ],

            // -------------------------------------------------
            // BLOOD PRESSURE
            // -------------------------------------------------
            [
                'name' => 'Amdocal 5 mg',
                'barcode' => '890100000043',
                'generic' => 'Amlodipine',
                'brand' => 'Amdocal',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 39,
                'sale_price' => 5,
                'strip_price' => 48,
            ],
            [
                'name' => 'Amdocal 10 mg',
                'barcode' => '890100000044',
                'generic' => 'Amlodipine',
                'brand' => 'Amdocal',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 60,
                'sale_price' => 8,
                'strip_price' => 75,
            ],
            [
                'name' => 'Amdocal 2.5 mg',
                'barcode' => '890100000045',
                'generic' => 'Amlodipine',
                'brand' => 'Amdocal',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 30,
                'sale_price' => 4,
                'strip_price' => 40,
            ],
            [
                'name' => 'Losar 50 mg',
                'barcode' => '890100000046',
                'generic' => 'Losartan',
                'brand' => 'Losar',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 70,
                'sale_price' => 10,
                'strip_price' => 90,
            ],
            [
                'name' => 'Telma 40 mg',
                'barcode' => '890100000047',
                'generic' => 'Telmisartan',
                'brand' => 'Telma',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 80,
                'sale_price' => 12,
                'strip_price' => 100,
            ],
            [
                'name' => 'Atenol 50 mg',
                'barcode' => '890100000048',
                'generic' => 'Atenolol',
                'brand' => 'Atenol',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 30,
                'sale_price' => 4,
                'strip_price' => 45,
            ],
            [
                'name' => 'Bisol 5 mg',
                'barcode' => '890100000049',
                'generic' => 'Bisoprolol',
                'brand' => 'Bisol',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Enap 5 mg',
                'barcode' => '890100000050',
                'generic' => 'Enalapril',
                'brand' => 'Enap',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Olmetec 20 mg',
                'barcode' => '890100000051',
                'generic' => 'Olmesartan',
                'brand' => 'Olmetec',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 125,
            ],

            // -------------------------------------------------
            // CHOLESTEROL / HEART
            // -------------------------------------------------
            [
                'name' => 'Atorva 10 mg',
                'barcode' => '890100000052',
                'generic' => 'Atorvastatin',
                'brand' => 'Atorva',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 45,
                'sale_price' => 7,
                'strip_price' => 60,
            ],
            [
                'name' => 'Atorva 20 mg',
                'barcode' => '890100000053',
                'generic' => 'Atorvastatin',
                'brand' => 'Atorva',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 70,
                'sale_price' => 10,
                'strip_price' => 90,
            ],
            [
                'name' => 'Rosuva 10 mg',
                'barcode' => '890100000054',
                'generic' => 'Rosuvastatin',
                'brand' => 'Rosuva',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 60,
                'sale_price' => 9,
                'strip_price' => 80,
            ],
            [
                'name' => 'Ecosprin 75 mg',
                'barcode' => '890100000055',
                'generic' => 'Aspirin',
                'brand' => 'Ecosprin',
                'pieces_per_strip' => 14,
                'reorder_level' => 30,
                'purchase_price' => 25,
                'sale_price' => 3,
                'strip_price' => 35,
            ],
            [
                'name' => 'Clopid 75 mg',
                'barcode' => '890100000056',
                'generic' => 'Clopidogrel',
                'brand' => 'Clopid',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 60,
                'sale_price' => 9,
                'strip_price' => 80,
            ],

            // -------------------------------------------------
            // VITAMINS / SUPPLEMENTS
            // -------------------------------------------------
            [
                'name' => 'Ceevit 500 mg',
                'barcode' => '890100000057',
                'generic' => 'Vitamin C',
                'brand' => 'Ceevit',
                'pieces_per_strip' => 10,
                'reorder_level' => 40,
                'purchase_price' => 18,
                'sale_price' => 2.5,
                'strip_price' => 25,
            ],
            [
                'name' => 'Ceevit 1000 mg',
                'barcode' => '890100000058',
                'generic' => 'Vitamin C',
                'brand' => 'Ceevit',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Calbo 500 mg',
                'barcode' => '890100000059',
                'generic' => 'Calcium Carbonate',
                'brand' => 'Calbo',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 50,
                'sale_price' => 7,
                'strip_price' => 65,
            ],
            [
                'name' => 'D-Care 20000 IU',
                'barcode' => '890100000060',
                'generic' => 'Vitamin D3',
                'brand' => 'D-Care',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 80,
                'sale_price' => 12,
                'strip_price' => 100,
            ],
            [
                'name' => 'Becosules',
                'barcode' => '890100000061',
                'generic' => 'Vitamin B Complex',
                'brand' => 'Becosules',
                'pieces_per_strip' => 20,
                'reorder_level' => 30,
                'purchase_price' => 100,
                'sale_price' => 7,
                'strip_price' => 120,
            ],
            [
                'name' => 'Ferrous 200 mg',
                'barcode' => '890100000062',
                'generic' => 'Iron',
                'brand' => 'Ferrous',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Folic 5 mg',
                'barcode' => '890100000063',
                'generic' => 'Folic Acid',
                'brand' => 'Folic',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 20,
                'sale_price' => 3,
                'strip_price' => 30,
            ],
            [
                'name' => 'Zincovit',
                'barcode' => '890100000064',
                'generic' => 'Zinc',
                'brand' => 'Zincovit',
                'pieces_per_strip' => 15,
                'reorder_level' => 20,
                'purchase_price' => 100,
                'sale_price' => 9,
                'strip_price' => 120,
            ],

            // -------------------------------------------------
            // RESPIRATORY
            // -------------------------------------------------
            [
                'name' => 'Ventolin 4 mg',
                'barcode' => '890100000065',
                'generic' => 'Salbutamol',
                'brand' => 'Ventolin',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Budicort 0.5 mg',
                'barcode' => '890100000066',
                'generic' => 'Budesonide',
                'brand' => 'Budicort',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 125,
            ],
            [
                'name' => 'Theo 100 mg',
                'barcode' => '890100000067',
                'generic' => 'Theophylline',
                'brand' => 'Theo',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Mucolyt 30 mg',
                'barcode' => '890100000068',
                'generic' => 'Ambroxol',
                'brand' => 'Mucolyt',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 35,
                'sale_price' => 5,
                'strip_price' => 50,
            ],
            [
                'name' => 'Bisolvon 8 mg',
                'barcode' => '890100000069',
                'generic' => 'Bromhexine',
                'brand' => 'Bisolvon',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 45,
            ],
            [
                'name' => 'Dexa Cough Syrup',
                'barcode' => '890100000070',
                'generic' => 'Dextromethorphan',
                'brand' => 'Dexa',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 70,
                'sale_price' => 100,
                'strip_price' => 100,
            ],

            // -------------------------------------------------
            // SKIN / ANTIFUNGAL
            // -------------------------------------------------
            [
                'name' => 'Bactroban 2%',
                'barcode' => '890100000071',
                'generic' => 'Mupirocin',
                'brand' => 'Bactroban',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 120,
                'sale_price' => 160,
                'strip_price' => 160,
            ],
            [
                'name' => 'Canesten 1%',
                'barcode' => '890100000072',
                'generic' => 'Clotrimazole',
                'brand' => 'Canesten',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 80,
                'sale_price' => 110,
                'strip_price' => 110,
            ],
            [
                'name' => 'Flucan 150 mg',
                'barcode' => '890100000073',
                'generic' => 'Fluconazole',
                'brand' => 'Flucan',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 20,
                'sale_price' => 30,
                'strip_price' => 30,
            ],
            [
                'name' => 'Nizoral 200 mg',
                'barcode' => '890100000074',
                'generic' => 'Ketoconazole',
                'brand' => 'Nizoral',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 130,
            ],
            [
                'name' => 'Hydrocort 1%',
                'barcode' => '890100000075',
                'generic' => 'Hydrocortisone',
                'brand' => 'Hydrocort',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 40,
                'sale_price' => 60,
                'strip_price' => 60,
            ],
            [
                'name' => 'Betnovate Cream',
                'barcode' => '890100000076',
                'generic' => 'Betamethasone',
                'brand' => 'Betnovate',
                'pieces_per_strip' => 1,
                'reorder_level' => 10,
                'purchase_price' => 50,
                'sale_price' => 70,
                'strip_price' => 70,
            ],

            // -------------------------------------------------
            // PAIN / INFLAMMATION
            // -------------------------------------------------
            [
                'name' => 'DP 400 mg',
                'barcode' => '890100000077',
                'generic' => 'Ibuprofen',
                'brand' => 'DP',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 45,
            ],
            [
                'name' => 'Voltaren 50 mg',
                'barcode' => '890100000078',
                'generic' => 'Diclofenac',
                'brand' => 'Voltaren',
                'pieces_per_strip' => 10,
                'reorder_level' => 30,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Naprosyn 250 mg',
                'barcode' => '890100000079',
                'generic' => 'Naproxen',
                'brand' => 'Naprosyn',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 45,
                'sale_price' => 7,
                'strip_price' => 60,
            ],
            [
                'name' => 'Tramal 50 mg',
                'barcode' => '890100000080',
                'generic' => 'Tramadol',
                'brand' => 'Tramal',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 80,
                'sale_price' => 12,
                'strip_price' => 100,
            ],
            [
                'name' => 'Ketorol 10 mg',
                'barcode' => '890100000081',
                'generic' => 'Ketorolac',
                'brand' => 'Ketorol',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],

            // -------------------------------------------------
            // STEROIDS
            // -------------------------------------------------
            [
                'name' => 'Dexa 0.5 mg',
                'barcode' => '890100000082',
                'generic' => 'Dexamethasone',
                'brand' => 'Dexa',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 25,
                'sale_price' => 4,
                'strip_price' => 35,
            ],
            [
                'name' => 'DPred 5 mg',
                'barcode' => '890100000083',
                'generic' => 'Prednisolone',
                'brand' => 'DPred',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 40,
            ],

            // -------------------------------------------------
            // ANTIPARASITIC
            // -------------------------------------------------
            [
                'name' => 'Flagyl 400 mg',
                'barcode' => '890100000084',
                'generic' => 'Metronidazole',
                'brand' => 'Flagyl',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 25,
                'sale_price' => 4,
                'strip_price' => 35,
            ],
            [
                'name' => 'Fasigyn 500 mg',
                'barcode' => '890100000085',
                'generic' => 'Tinidazole',
                'brand' => 'Fasigyn',
                'pieces_per_strip' => 4,
                'reorder_level' => 10,
                'purchase_price' => 40,
                'sale_price' => 8,
                'strip_price' => 45,
            ],

            // -------------------------------------------------
            // ANTIVIRAL
            // -------------------------------------------------
            [
                'name' => 'Zovirax 400 mg',
                'barcode' => '890100000086',
                'generic' => 'Acyclovir',
                'brand' => 'Zovirax',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 150,
                'sale_price' => 22,
                'strip_price' => 190,
            ],
            [
                'name' => 'Valtrex 500 mg',
                'barcode' => '890100000087',
                'generic' => 'Valacyclovir',
                'brand' => 'Valtrex',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 250,
                'sale_price' => 35,
                'strip_price' => 300,
            ],

            // -------------------------------------------------
            // NERVE / NEURO
            // -------------------------------------------------
            [
                'name' => 'Pregaba 75 mg',
                'barcode' => '890100000088',
                'generic' => 'Pregabalin',
                'brand' => 'Pregaba',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 125,
            ],
            [
                'name' => 'Neurontin 300 mg',
                'barcode' => '890100000089',
                'generic' => 'Gabapentin',
                'brand' => 'Neurontin',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 150,
                'sale_price' => 22,
                'strip_price' => 190,
            ],

            // -------------------------------------------------
            // MISC
            // -------------------------------------------------
            [
                'name' => 'Piriton 4 mg',
                'barcode' => '890100000090',
                'generic' => 'Chlorpheniramine',
                'brand' => 'Piriton',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 20,
                'sale_price' => 3,
                'strip_price' => 30,
            ],
            [
                'name' => 'DPH 25 mg',
                'barcode' => '890100000091',
                'generic' => 'Diphenhydramine',
                'brand' => 'DPH',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 25,
                'sale_price' => 4,
                'strip_price' => 35,
            ],
            [
                'name' => 'Uromax 0.4 mg',
                'barcode' => '890100000092',
                'generic' => 'Tamsulosin',
                'brand' => 'Uromax',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 100,
                'sale_price' => 15,
                'strip_price' => 125,
            ],
            [
                'name' => 'Finast 5 mg',
                'barcode' => '890100000093',
                'generic' => 'Finasteride',
                'brand' => 'Finast',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 80,
                'sale_price' => 12,
                'strip_price' => 100,
            ],
            [
                'name' => 'Viagra 50 mg',
                'barcode' => '890100000094',
                'generic' => 'Sildenafil',
                'brand' => 'Viagra',
                'pieces_per_strip' => 4,
                'reorder_level' => 5,
                'purchase_price' => 200,
                'sale_price' => 300,
                'strip_price' => 300,
            ],
            [
                'name' => 'Cialis 10 mg',
                'barcode' => '890100000095',
                'generic' => 'Tadalafil',
                'brand' => 'Cialis',
                'pieces_per_strip' => 4,
                'reorder_level' => 5,
                'purchase_price' => 250,
                'sale_price' => 350,
                'strip_price' => 350,
            ],

            // -------------------------------------------------
            // STOMACH / DIGESTIVE
            // -------------------------------------------------
            [
                'name' => 'Domper 10 mg',
                'barcode' => '890100000096',
                'generic' => 'Domperidone',
                'brand' => 'Domper',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 45,
            ],
            [
                'name' => 'Onset 8 mg',
                'barcode' => '890100000097',
                'generic' => 'Ondansetron',
                'brand' => 'Onset',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 60,
                'sale_price' => 9,
                'strip_price' => 80,
            ],
            [
                'name' => 'Sucral 1 g',
                'barcode' => '890100000098',
                'generic' => 'Sucralfate',
                'brand' => 'Sucral',
                'pieces_per_strip' => 10,
                'reorder_level' => 20,
                'purchase_price' => 70,
                'sale_price' => 10,
                'strip_price' => 90,
            ],
            [
                'name' => 'Imodium 2 mg',
                'barcode' => '890100000099',
                'generic' => 'Loperamide',
                'brand' => 'Imodium',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 40,
                'sale_price' => 6,
                'strip_price' => 55,
            ],
            [
                'name' => 'Dulcolax 5 mg',
                'barcode' => '890100000100',
                'generic' => 'Bisacodyl',
                'brand' => 'Dulcolax',
                'pieces_per_strip' => 10,
                'reorder_level' => 10,
                'purchase_price' => 30,
                'sale_price' => 5,
                'strip_price' => 45,
            ],

            // -------------------------------------------------
            // ORS / GENERAL
            // -------------------------------------------------
            [
                'name' => 'Orsaline-N',
                'barcode' => '890100000101',
                'generic' => 'ORS',
                'brand' => 'Orsaline',
                'pieces_per_strip' => 1,
                'reorder_level' => 30,
                'purchase_price' => 5,
                'sale_price' => 8,
                'strip_price' => 8,
            ],
            [
                'name' => 'Glucose-D',
                'barcode' => '890100000102',
                'generic' => 'Glucose',
                'brand' => 'Glucose-D',
                'pieces_per_strip' => 1,
                'reorder_level' => 20,
                'purchase_price' => 40,
                'sale_price' => 55,
                'strip_price' => 55,
            ],
        ];

        $result = [];

        foreach ($products as $data) {

            // Safety check for generic
            if (!isset($generics[$data['generic']])) {
                throw new \RuntimeException(
                    "Generic name '{$data['generic']}' is not seeded for product '{$data['name']}'."
                );
            }

            // Safety check for brand
            if (!isset($brands[$data['brand']])) {
                throw new \RuntimeException(
                    "Brand '{$data['brand']}' is not seeded for product '{$data['name']}'."
                );
            }

            $product = Product::updateOrCreate(
                [
                    'barcode' => $data['barcode'],
                ],
                [
                    'name' => $data['name'],

                    // Database stores foreign keys
                    'generic_name_id' => $generics[$data['generic']]->id,
                    'brand_id' => $brands[$data['brand']]->id,

                    'unit' => 'piece',
                    'pieces_per_strip' => $data['pieces_per_strip'],
                    'sell_by_piece' => true,
                    'sell_by_strip' => true,
                    'reorder_level' => $data['reorder_level'],
                    'is_active' => true,
                ]
            );

            $result[$data['name']] = $product;
        }

        return $result;
    }

    /**
     * ---------------------------------------------------------
     * CUSTOMERS
     * ---------------------------------------------------------
     */
    private function seedCustomers(): void
    {
        $customers = [
            [
                'name' => 'Rahim Pharmacy',
                'customer_type' => 'wholesale',
                'phone' => '01714000001',
                'email' => 'rahim@customer.example',
                'address' => 'Mirpur, Dhaka',
                'due_balance' => 1200,
            ],
            [
                'name' => 'City Medicine Corner',
                'customer_type' => 'wholesale',
                'phone' => '01714000002',
                'email' => 'city@customer.example',
                'address' => 'Uttara, Dhaka',
                'due_balance' => 0,
            ],
            [
                'name' => 'Nusrat Jahan',
                'customer_type' => 'retail',
                'phone' => '01714000003',
                'email' => 'nusrat@customer.example',
                'address' => 'Dhanmondi, Dhaka',
                'due_balance' => 150,
            ],
            [
                'name' => 'Karim Uddin',
                'customer_type' => 'retail',
                'phone' => '01714000004',
                'email' => 'karim@customer.example',
                'address' => 'Mohammadpur, Dhaka',
                'due_balance' => 0,
            ],
            [
                'name' => 'Popular Medicine Store',
                'customer_type' => 'wholesale',
                'phone' => '01714000005',
                'email' => 'popular@customer.example',
                'address' => 'Farmgate, Dhaka',
                'due_balance' => 850,
            ],
            [
                'name' => 'Green Pharmacy',
                'customer_type' => 'wholesale',
                'phone' => '01714000006',
                'email' => 'green@customer.example',
                'address' => 'Gulshan, Dhaka',
                'due_balance' => 500,
            ],
            [
                'name' => 'Sadia Akter',
                'customer_type' => 'retail',
                'phone' => '01714000007',
                'email' => 'sadia@customer.example',
                'address' => 'Banani, Dhaka',
                'due_balance' => 0,
            ],
            [
                'name' => 'Hasan Mahmud',
                'customer_type' => 'retail',
                'phone' => '01714000008',
                'email' => 'hasan@customer.example',
                'address' => 'Mirpur, Dhaka',
                'due_balance' => 250,
            ],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(
                [
                    'phone' => $data['phone'],
                ],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * ---------------------------------------------------------
     * PURCHASES
     * ---------------------------------------------------------
     */
    private function seedPurchases(
        array $products,
        array $suppliers,
        User $admin
    ): void {
        $productNames = array_keys($products);

        /*
         * 102 products / 10 products per purchase
         * = around 11 purchase invoices.
         */
        $purchaseGroups = array_chunk($productNames, 10);

        $supplierNames = array_keys($suppliers);

        foreach ($purchaseGroups as $purchaseIndex => $group) {

            $invoiceNumber = 'PUR-DEMO-' . str_pad(
                (string) ($purchaseIndex + 1),
                3,
                '0',
                STR_PAD_LEFT
            );

            /*
             * If this invoice already exists, don't create it again.
             */
            if (
                Purchase::query()
                ->where('invoice_number', $invoiceNumber)
                ->exists()
            ) {
                continue;
            }

            $supplierName = $supplierNames[$purchaseIndex % count($supplierNames)];

            $items = [];

            foreach ($group as $productName) {

                $product = $products[$productName];

                /*
                 * Use the product's actual defined purchase price
                 * instead of generating a random price.
                 *
                 * The original code was using:
                 *
                 * rand(20, 200)
                 *
                 * which ignored your product pricing.
                 */
                $purchasePrice = match (true) {
                    isset($product->purchase_price) &&
                        is_numeric($product->purchase_price)
                    => (float) $product->purchase_price,

                    default
                    => rand(20, 200),
                };

                /*
                 * If Product doesn't have purchase_price,
                 * calculate a demo price.
                 */
                if ($purchasePrice <= 0) {
                    $purchasePrice = rand(20, 200);
                }

                $salePrice = round(
                    $purchasePrice /
                        max($product->pieces_per_strip, 1) *
                        1.5,
                    2
                );

                $stripPrice = round(
                    $salePrice *
                        max($product->pieces_per_strip, 1),
                    2
                );

                $quantity = rand(10, 50);

                $items[] = [
                    'product' => $productName,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                    'strip_price' => $stripPrice,
                ];
            }

            DB::transaction(function () use (
                $invoiceNumber,
                $supplierName,
                $items,
                $products,
                $suppliers,
                $admin,
                $purchaseIndex
            ) {

                $subtotal = collect($items)->sum(
                    fn(array $item) =>
                    $item['quantity'] *
                        $item['purchase_price']
                );

                $purchase = Purchase::create([
                    'invoice_number' => $invoiceNumber,

                    'supplier_id' => $suppliers[$supplierName]->id,

                    'user_id' => $admin->id,

                    'subtotal' => $subtotal,

                    'discount' => 0,

                    'total' => $subtotal,

                    'paid' => $subtotal,

                    'purchased_at' => now()
                        ->subDays(
                            max(
                                1,
                                30 - ($purchaseIndex * 2)
                            )
                        )
                        ->toDateString(),
                ]);

                foreach ($items as $index => $item) {

                    $product = $products[$item['product']];

                    /*
                     * Quantity is purchased in strips.
                     *
                     * Example:
                     * quantity = 20 strips
                     * pieces_per_strip = 10
                     *
                     * stock = 200 pieces
                     */
                    $stockQuantity =
                        $item['quantity'] *
                        max($product->pieces_per_strip, 1);

                    /*
                     * Convert strip purchase price
                     * into piece cost.
                     */
                    $unitCost = round(
                        $item['purchase_price'] /
                            max($product->pieces_per_strip, 1),
                        4
                    );

                    $batchNumber =
                        'DEMO-' .
                        str_pad(
                            (string) ($purchaseIndex + 1),
                            3,
                            '0',
                            STR_PAD_LEFT
                        ) .
                        '-' .
                        str_pad(
                            (string) ($index + 1),
                            2,
                            '0',
                            STR_PAD_LEFT
                        );

                    $batch = MedicineBatch::create([
                        'product_id' => $product->id,

                        'supplier_id' =>
                        $suppliers[$supplierName]->id,

                        'batch_number' => $batchNumber,

                        'expires_on' => now()
                            ->addMonths(
                                12 + ($index % 12)
                            )
                            ->toDateString(),

                        'quantity_received' => $stockQuantity,

                        'quantity_available' => $stockQuantity,

                        'purchase_price' => $unitCost,

                        'sale_price' => $item['sale_price'],

                        'strip_sale_price' => $item['strip_price'],
                    ]);

                    $purchaseItem = $purchase->items()->create([
                        'product_id' => $product->id,

                        'medicine_batch_id' => $batch->id,

                        /*
                         * Number of strips purchased.
                         */
                        'quantity' => $item['quantity'],

                        'purchase_unit' => 'strip',

                        'units_per_purchase_unit' =>
                        $product->pieces_per_strip,

                        /*
                         * Actual stock in pieces.
                         */
                        'stock_quantity' => $stockQuantity,

                        /*
                         * Price for one strip.
                         */
                        'purchase_price' =>
                        $item['purchase_price'],

                        /*
                         * Piece sale price.
                         */
                        'sale_price' =>
                        $item['sale_price'],

                        /*
                         * Strip sale price.
                         */
                        'strip_sale_price' =>
                        $item['strip_price'],

                        'line_total' =>
                        $item['quantity'] *
                            $item['purchase_price'],
                    ]);

                    /*
                     * Stock movement.
                     */
                    StockMovement::create([
                        'product_id' => $product->id,

                        'medicine_batch_id' => $batch->id,

                        'user_id' => $admin->id,

                        'type' => 'purchase',

                        'quantity_change' => $stockQuantity,

                        'reference_type' =>
                        $purchaseItem->getMorphClass(),

                        'reference_id' =>
                        $purchaseItem->id,

                        'notes' =>
                        'Demo stock received through ' .
                            $purchase->invoice_number,
                    ]);
                }
            });
        }
    }
}
