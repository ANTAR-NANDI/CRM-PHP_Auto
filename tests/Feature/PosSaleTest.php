<?php

namespace Tests\Feature;

use App\Models\MedicineBatch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_sell_strips_and_base_piece_stock_is_deducted(): void
    {
        [$cashier, $product, $batch] = $this->saleSetup(25);

        $response = $this->actingAs($cashier)->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'sale_unit' => 'strip', 'quantity' => 2]],
            'customer_type' => 'walking',
            'discount' => 0,
            'paid' => 60,
            'payment_method' => 'cash',
        ]);

        $sale = Sale::firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('sales', ['total' => 56, 'paid' => 60, 'user_id' => $cashier->id]);
        $this->assertDatabaseHas('sale_items', ['quantity' => 2, 'sale_unit' => 'strip', 'units_per_sale_unit' => 10, 'stock_quantity' => 20]);
        $this->assertSame(5, $batch->fresh()->quantity_available);
        $this->assertDatabaseHas('stock_movements', ['type' => 'sale', 'quantity_change' => -20]);
    }

    public function test_cashier_can_sell_single_pieces(): void
    {
        [$cashier, $product, $batch] = $this->saleSetup(25);

        $this->actingAs($cashier)->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'sale_unit' => 'piece', 'quantity' => 3]],
            'customer_type' => 'walking',
            'discount' => 1,
            'paid' => 8,
            'payment_method' => 'cash',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sales', ['subtotal' => 9, 'discount' => 1, 'total' => 8]);
        $this->assertSame(22, $batch->fresh()->quantity_available);
    }

    public function test_insufficient_stock_rejects_sale_without_deducting_inventory(): void
    {
        [$cashier, $product, $batch] = $this->saleSetup(5);

        $response = $this->actingAs($cashier)->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'sale_unit' => 'strip', 'quantity' => 1]],
            'customer_type' => 'walking',
            'discount' => 0,
            'paid' => 28,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseEmpty('sales');
        $this->assertSame(5, $batch->fresh()->quantity_available);
    }

    public function test_wholesale_sale_requires_saved_customer_and_can_add_due_balance(): void
    {
        [$cashier, $product] = $this->saleSetup(20);
        $customer = Customer::create([
            'name' => 'Wholesale Buyer', 'customer_type' => 'wholesale', 'phone' => '01711111111',
            'due_balance' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('pos.store'), [
            'items' => [['product_id' => $product->id, 'sale_unit' => 'strip', 'quantity' => 1]],
            'customer_type' => 'wholesale',
            'customer_id' => $customer->id,
            'discount' => 0,
            'paid' => 8,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sales', ['customer_id' => $customer->id, 'customer_type' => 'wholesale', 'total' => 28, 'paid' => 8, 'due' => 20]);
        $this->assertSame('30.00', $customer->fresh()->due_balance);
    }

    private function saleSetup(int $stock): array
    {
        $cashier = User::factory()->create(['is_active' => true]);
        $cashier->assignRole(Role::create(['name' => 'cashier', 'guard_name' => 'web']));
        $product = Product::create([
            'name' => 'Napa Tablet', 'unit' => 'piece', 'pieces_per_strip' => 10,
            'sell_by_piece' => true, 'sell_by_strip' => true, 'reorder_level' => 5, 'is_active' => true,
        ]);
        $batch = MedicineBatch::create([
            'product_id' => $product->id, 'batch_number' => 'N-01', 'expires_on' => today()->addYear(),
            'quantity_received' => $stock, 'quantity_available' => $stock,
            'purchase_price' => 2, 'sale_price' => 3, 'strip_sale_price' => 28,
        ]);

        return [$cashier, $product, $batch];
    }
}
