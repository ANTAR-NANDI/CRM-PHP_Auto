<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_receive_a_purchase_and_stock_is_created(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $supplier = Supplier::create(['name' => 'Test Supplier', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Test Medicine',
            'unit' => 'piece',
            'pieces_per_strip' => 10,
            'sell_by_piece' => true,
            'sell_by_strip' => true,
            'reorder_level' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
            'discount' => 20,
            'paid' => 180,
            'items' => [[
                'product_id' => $product->id,
                'batch_number' => 'B-100',
                'expires_on' => now()->addYear()->toDateString(),
                'quantity' => 10,
                'purchase_unit' => 'strip',
                'purchase_price' => 20,
                'single_sale_price' => 2.50,
                'strip_sale_price' => 25,
            ]],
        ]);

        $purchase = \App\Models\Purchase::firstOrFail();

        $response->assertSessionHasNoErrors()->assertRedirect(route('purchases.show', $purchase));
        $this->assertDatabaseHas('purchases', ['subtotal' => 200, 'discount' => 20, 'total' => 180, 'paid' => 180]);
        $this->assertDatabaseHas('purchase_items', ['product_id' => $product->id, 'quantity' => 10, 'purchase_unit' => 'strip', 'stock_quantity' => 100]);
        $this->assertDatabaseHas('medicine_batches', ['product_id' => $product->id, 'quantity_received' => 100, 'quantity_available' => 100, 'purchase_price' => 2, 'strip_sale_price' => 25]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'purchase', 'quantity_change' => 100]);
    }

    public function test_sale_price_cannot_be_lower_than_purchase_price(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $supplier = Supplier::create(['name' => 'Test Supplier', 'is_active' => true]);
        $product = Product::create(['name' => 'Test Medicine', 'unit' => 'piece', 'pieces_per_strip' => 10, 'sell_by_piece' => true, 'sell_by_strip' => true, 'reorder_level' => 5, 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'purchase_unit' => 'strip',
                'purchase_price' => 20,
                'single_sale_price' => 1.90,
                'strip_sale_price' => 25,
            ]],
        ]);

        $response->assertSessionHasErrors('items.0.single_sale_price');
        $this->assertDatabaseEmpty('purchases');
        $this->assertDatabaseEmpty('medicine_batches');
    }
}
