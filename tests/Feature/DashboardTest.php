<?php

namespace Tests\Feature;

use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_live_stock_information(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $product = Product::create([
            'name' => 'Low Stock Medicine', 'unit' => 'piece', 'pieces_per_strip' => 10,
            'sell_by_piece' => true, 'sell_by_strip' => true, 'reorder_level' => 20, 'is_active' => true,
        ]);
        MedicineBatch::create([
            'product_id' => $product->id, 'quantity_received' => 10, 'quantity_available' => 10,
            'purchase_price' => 2, 'sale_price' => 3, 'strip_sale_price' => 30,
            'expires_on' => today()->addMonth(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Low Stock Medicine')
            ->assertSee('10 pieces')
            ->assertSee('Stock purchase value');
    }

    public function test_cashier_dashboard_does_not_show_admin_employee_action(): void
    {
        $cashier = User::factory()->create(['is_active' => true]);
        $cashier->assignRole(Role::create(['name' => 'cashier', 'guard_name' => 'web']));

        $this->actingAs($cashier)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Add employee');
    }
}
