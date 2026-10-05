<?php

namespace Tests\Feature;

use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_employee_can_login_with_employee_code(): void
    {
        $cashier = $this->cashier(['employee_code' => 'EMP-001']);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'EMP-001',
            'password' => 'password',
            'device_name' => 'Test phone',
        ])->assertOk()
            ->assertJsonPath('data.user.id', $cashier->id)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'employee_code', 'roles']]]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_mobile_catalog_returns_strip_and_piece_stock(): void
    {
        $cashier = $this->cashier();
        [$product] = $this->stockedProduct(25);
        Sanctum::actingAs($cashier, ['mobile-pos']);

        $this->getJson('/api/v1/catalog/products?search=Napa')
            ->assertOk()
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.piece_stock', 25)
            ->assertJsonPath('data.0.strip_stock', 2)
            ->assertJsonPath('data.0.piece_price', 3)
            ->assertJsonPath('data.0.strip_price', 28)
            ->assertJsonPath('data.0.batches.0.piece_stock', 25)
            ->assertJsonPath('data.0.batches.0.strip_price', 28);
    }

    public function test_employee_can_complete_mobile_strip_sale(): void
    {
        $cashier = $this->cashier();
        [$product, $batch] = $this->stockedProduct(25);
        Sanctum::actingAs($cashier, ['mobile-pos']);

        $this->postJson('/api/v1/sales', [
            'items' => [['product_id' => $product->id, 'sale_unit' => 'strip', 'quantity' => 2]],
            'customer_type' => 'walking',
            'discount' => 1,
            'paid' => 60,
            'payment_method' => 'cash',
        ])->assertCreated()
            ->assertJsonPath('data.total', 55)
            ->assertJsonPath('data.items.0.sale_unit', 'strip')
            ->assertJsonPath('data.employee.id', $cashier->id);

        $this->assertSame(5, $batch->fresh()->quantity_available);
        $this->assertDatabaseHas('stock_movements', ['quantity_change' => -20, 'type' => 'sale']);
    }

    public function test_salesperson_can_sell_and_view_stock_but_cannot_view_team_sales(): void
    {
        $salesperson = User::factory()->create(['is_active' => true]);
        $salesperson->assignRole(Role::firstOrCreate(['name' => 'salesperson', 'guard_name' => 'web']));
        Sanctum::actingAs($salesperson, ['mobile-pos']);

        $this->getJson('/api/v1/stock')->assertOk();
        $this->getJson('/api/v1/sales')->assertForbidden();
    }

    private function cashier(array $attributes = []): User
    {
        $cashier = User::factory()->create([...$attributes, 'is_active' => true]);
        $cashier->assignRole(Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']));

        return $cashier;
    }

    private function stockedProduct(int $stock): array
    {
        $product = Product::create([
            'name' => 'Napa Tablet', 'barcode' => '1234567890', 'unit' => 'piece', 'pieces_per_strip' => 10,
            'sell_by_piece' => true, 'sell_by_strip' => true, 'reorder_level' => 5, 'is_active' => true,
        ]);
        $batch = MedicineBatch::create([
            'product_id' => $product->id, 'batch_number' => 'N-01', 'expires_on' => today()->addYear(),
            'quantity_received' => $stock, 'quantity_available' => $stock,
            'purchase_price' => 2, 'sale_price' => 3, 'strip_sale_price' => 28,
        ]);

        return [$product, $batch];
    }
}
