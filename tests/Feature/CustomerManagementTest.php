<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_save_a_fixed_retail_customer(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole(Role::create(['name' => 'cashier', 'guard_name' => 'web']));

        $response = $this->actingAs($manager)->post(route('customers.store'), [
            'name' => 'Regular Retail Buyer',
            'customer_type' => 'retail',
            'phone' => '01800000000',
            'email' => 'buyer@example.com',
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['name' => 'Regular Retail Buyer', 'customer_type' => 'retail', 'is_active' => true]);
    }
}
