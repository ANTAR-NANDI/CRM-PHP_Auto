<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_pharmacy_reference_data_stock_and_employees(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('suppliers', 3);
        $this->assertDatabaseCount('generic_names', 8);
        $this->assertDatabaseCount('brands', 9);
        $this->assertDatabaseCount('products', 9);
        $this->assertDatabaseCount('customers', 4);
        $this->assertDatabaseCount('purchases', 3);
        $this->assertDatabaseCount('medicine_batches', 9);
        $this->assertDatabaseCount('purchase_items', 9);
        $this->assertDatabaseCount('stock_movements', 9);
        $this->assertDatabaseHas('users', ['employee_code' => 'EMP-00003', 'email' => 'cashier@pharmacy.test']);
        $this->assertDatabaseHas('products', ['name' => 'Napa 500 mg', 'pieces_per_strip' => 10, 'sell_by_strip' => true]);
    }
}
