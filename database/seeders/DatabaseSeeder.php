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
            // Intentionally only creates the initial administrator. All master,
            // employee, inventory, CRM, sales, and accounting data is entered
            // manually through the application.
            BootstrapAdminSeeder::class,
            RolesAndPermissionsSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);
    }
}
