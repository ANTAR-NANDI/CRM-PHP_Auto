<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view', 'employees.manage',
            'suppliers.manage', 'brands.manage', 'generic-names.manage',
            'products.view', 'products.manage', 'purchases.manage', 'customers.manage', 'stock.adjust',
            'pos.access', 'sales.view-own', 'sales.view-all', 'sales.return',
            'reports.view', 'accounts.manage', 'settings.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'events.manage', 'budgets.manage', 'bulk-sms.manage', 'pre-event-plans.manage', 'agendas.manage', 'meetings.manage', 'sales-targets.manage', 'sales.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $manager = Role::findOrCreate('manager');
        $cashier = Role::findOrCreate('cashier');
        $salesperson = Role::findOrCreate('salesperson');

        $admin->syncPermissions($permissions);
        $manager->syncPermissions([
            'dashboard.view', 'employees.manage', 'suppliers.manage', 'brands.manage',
            'generic-names.manage', 'products.view', 'products.manage', 'purchases.manage',
            'customers.manage', 'stock.adjust', 'pos.access', 'sales.view-own', 'sales.view-all',
            'sales.return', 'reports.view', 'accounts.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'bulk-sms.manage',
        ]);
        $cashier->syncPermissions([
            'dashboard.view', 'suppliers.manage', 'products.view', 'purchases.manage',
            'customers.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'bulk-sms.manage', 'pos.access', 'sales.view-own', 'sales.view-all',
        ]);
        $salesperson->syncPermissions(['dashboard.view', 'products.view', 'activities.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'pos.access', 'sales.view-own']);

        foreach ([
            ['name' => 'System Admin', 'email' => 'admin@pharmacy.test', 'employee_code' => 'EMP-00001', 'designation' => 'Administrator', 'role' => $admin],
            ['name' => 'Store Manager', 'email' => 'manager@pharmacy.test', 'employee_code' => 'EMP-00002', 'designation' => 'Pharmacy Manager', 'role' => $manager],
            ['name' => 'Salesperson One', 'email' => 'salesperson@pharmacy.test', 'employee_code' => 'EMP-00003', 'designation' => 'Salesperson', 'role' => $salesperson],
            ['name' => 'Salesperson Two', 'email' => 'salesperson2@pharmacy.test', 'employee_code' => 'EMP-00004', 'designation' => 'Salesperson', 'role' => $salesperson],
            ['name' => 'Salesperson Three', 'email' => 'salesperson3@pharmacy.test', 'employee_code' => 'EMP-00005', 'designation' => 'Salesperson', 'role' => $salesperson],
            ['name' => 'Salesperson Four', 'email' => 'salesperson4@pharmacy.test', 'employee_code' => 'EMP-00006', 'designation' => 'Salesperson', 'role' => $salesperson],
        ] as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'employee_code' => $account['employee_code'],
                    'designation' => $account['designation'],
                    'password' => Hash::make('password'),
                    'role' => $account['role']->name,
                    'is_active' => true,
                ]
            );
            $user->syncRoles([$account['role']]);
        }
    }
}
