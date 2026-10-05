<?php

namespace Tests\Feature;

use App\Models\MedicineBatch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_pharmacy_reports_with_live_data(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));
        $supplier = Supplier::create(['name' => 'Report Supplier', 'is_active' => true]);
        Purchase::create([
            'invoice_number' => 'PUR-REPORT', 'supplier_id' => $supplier->id, 'user_id' => $manager->id,
            'subtotal' => 100, 'discount' => 10, 'total' => 90, 'paid' => 50, 'purchased_at' => today(),
        ]);
        $product = Product::create([
            'name' => 'Needed Medicine', 'unit' => 'piece', 'pieces_per_strip' => 10,
            'sell_by_piece' => true, 'sell_by_strip' => true, 'reorder_level' => 10, 'is_active' => true,
        ]);
        $batch = MedicineBatch::create([
            'product_id' => $product->id, 'supplier_id' => $supplier->id, 'quantity_received' => 5,
            'quantity_available' => 5, 'purchase_price' => 3, 'sale_price' => 5, 'strip_sale_price' => 50,
        ]);
        $sale = Sale::create([
            'invoice_number' => 'SAL-REPORT', 'user_id' => $manager->id, 'subtotal' => 15,
            'discount' => 0, 'total' => 15, 'paid' => 15, 'due' => 0, 'payment_method' => 'cash', 'sold_at' => now(),
        ]);
        $sale->items()->create([
            'product_id' => $product->id, 'medicine_batch_id' => $batch->id, 'quantity' => 3,
            'sale_unit' => 'piece', 'units_per_sale_unit' => 1, 'stock_quantity' => 3,
            'unit_purchase_price' => 3, 'unit_sale_price' => 5, 'line_total' => 15,
        ]);

        $this->actingAs($manager)->get(route('reports.index'))->assertOk()->assertSee('Pharmacy Reports');
        $this->actingAs($manager)->get(route('reports.purchases'))->assertOk()->assertSee('PUR-REPORT')->assertSee('Report Supplier');
        $this->actingAs($manager)->get(route('reports.sales'))->assertOk()->assertSee('SAL-REPORT')->assertSee('Gross profit');
        $this->actingAs($manager)->get(route('reports.stock'))->assertOk()->assertSee('Needed Medicine')->assertSee('Available pieces');
        $this->actingAs($manager)->get(route('reports.needed'))->assertOk()->assertSee('Needed Medicine')->assertSee('Suggested Order');
    }

    public function test_cashier_cannot_access_management_reports(): void
    {
        $cashier = User::factory()->create(['is_active' => true]);
        $cashier->assignRole(Role::create(['name' => 'cashier', 'guard_name' => 'web']));

        $this->actingAs($cashier)->get(route('reports.index'))->assertForbidden();
    }
}
