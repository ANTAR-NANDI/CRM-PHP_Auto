<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::query()->where('guard_name', 'web')->get()->keyBy('name');

        $rolePermissions = [
            'admin' => $permissions->keys()->all(),
            'manager' => [
                'dashboard.view', 'employees.manage', 'config.employees.manage', 'config.users.manage', 'config.stores.manage', 'config.store-positions.manage',
                'suppliers.manage', 'brands.manage', 'generic-names.manage', 'products.view', 'products.manage', 'purchases.manage', 'customers.manage', 'stock.adjust', 'pos.access', 'sales.view-own', 'sales.view-all', 'sales.return', 'reports.view', 'accounts.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'bulk-sms.manage',
            ],
            'cashier' => [
                'dashboard.view', 'suppliers.manage', 'products.view', 'purchases.manage', 'customers.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'bulk-sms.manage', 'pos.access', 'sales.view-own', 'sales.view-all',
            ],
            'salesperson' => [
                'dashboard.view', 'products.view', 'activities.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'pos.access', 'sales.view-own',
            ],
        ];

        $roles = [];
        foreach ($rolePermissions as $name => $names) {
            $roles[$name] = Role::findOrCreate($name, 'web');
            $roles[$name]->syncPermissions($permissions->only($names)->values());
        }

        foreach ([
            ['name' => 'Tanvir Hasan', 'employee_code' => 'EMP-1001', 'email' => 'tanvir@example.test', 'phone' => '01711030001', 'designation' => 'Sales Executive', 'role' => 'salesperson'],
            ['name' => 'Rupa Sultana', 'employee_code' => 'EMP-1002', 'email' => 'rupa@example.test', 'phone' => '01711030002', 'designation' => 'Marketing Executive', 'role' => 'manager'],
            ['name' => 'Imran Kabir', 'employee_code' => 'EMP-1003', 'email' => 'imran@example.test', 'phone' => '01711030003', 'designation' => 'Inventory Officer', 'role' => 'manager'],
            ['name' => 'Nabila Rahman', 'employee_code' => 'EMP-1004', 'email' => 'nabila@example.test', 'phone' => '01711030004', 'designation' => 'Accounts Officer', 'role' => 'cashier'],
        ] as $employee) {
            $user = User::query()->updateOrCreate(
                ['email' => $employee['email']],
                $employee + ['password' => Hash::make('password'), 'is_active' => true, 'email_verified_at' => now()],
            );
            $user->syncRoles([$roles[$employee['role']]]);
        }
    }
}
