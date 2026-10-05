<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Security and account structure must exist before employee and purchase entries.
            RolesAndPermissionsSeeder::class,
            ActivityTypeSeeder::class,
            CrmLeadLookupSeeder::class,
            TodoTypeSeeder::class,
            ContactLookupSeeder::class,
            ChartOfAccountsSeeder::class,

            // Master catalogue data supplied for this pharmacy.
            BangladeshPharmaceuticalSuppliersSeeder::class,
            PharmacyGenericNamesSeeder::class,
            PharmacyMedicineListASeeder::class,
            PharmacyMedicineListBSeeder::class,
            PharmacyMedicineListCSeeder::class,
            PharmacyMedicineRealDSeeder::class,
            PharmacyMedicineRealESeeder::class,
            PharmacyMedicineRealFtoJSeeder::class,
            PharmacyMedicineRealKtoPSeeder::class,
            PharmacyMedicineRealQtoVSeeder::class,

            // Creates purchase invoices, batches, POS stock and accounting postings.
            PharmacyCatalogPur