<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_required_permission_can_open_protected_module(): void
    {
        Permission::findOrCreate('products.view');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('products.view');

        $this->actingAs($user)->get('/admin/products')->assertOk();
    }

    public function test_user_without_required_permission_is_denied(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/admin/products')->assertForbidden();
    }

    public function test_inactive_user_is_denied_even_when_permission_was_assigned(): void
    {
        Permission::findOrCreate('products.view');
        $user = User::factory()->create(['is_active' => false]);
        $user->givePermissionTo('products.view');

        $this->actingAs($user)->get('/admin/products')->assertForbidden();
    }
}
