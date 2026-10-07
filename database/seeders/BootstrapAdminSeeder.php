<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class BootstrapAdminSeeder extends Seeder
{
    /**
     * Creates the one required initial administrator. It intentionally does not
     * seed demo/master data.
     */
    public function run(): void
    {
        $role = Role::findOrCreate('admin', 'web');

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@w3crm.com'],
            [
                'name' => 'System Administrator',
                'password' => 'admin123',
                'role' => 'admin',
                'designation' => 'System Admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);
    }
}
