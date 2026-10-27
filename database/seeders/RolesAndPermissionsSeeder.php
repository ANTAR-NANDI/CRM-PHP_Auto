<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            'config.roles.manage', 'config.employees.manage', 'config.users.manage', 'config.stores.manage', 'config.store-positions.manage',
            'system-settings.manage',
            'suppliers.manage', 'brands.manage', 'generic-names.manage',
            'products.view', 'products.manage', 'purchases.manage', 'customers.manage', 'stock.adjust',
            'pos.access', 'sales.view-own', 'sales.view-all', 'sales.return',
            'reports.view', 'accounts.manage', 'settings.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'events.manage', 'budgets.manage', 'bulk-sms.manage', 'pre-event-plans.manage', 'agendas.manage', 'meetings.manage', 'sales-targets.manage', 'sales.manage',
        ];

        $permissions = array_values(array_unique($permissions));
        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findOrCreate('admin');
        $manager = Role::findOrCreate('manager');
        $salesExecutive = Role::findOrCreate('sales-executive');
        $marketingExecutive = Role::findOrCreate('marketing-executive');
        $account = Role::findOrCreate('account');

        $admin->syncPermissions($permissions);
        $manager->syncPermissions([
            'dashboard.view',
            'config.stores.manage', 'config.store-positions.manage', 'config.employees.manage',
            'system-settings.manage',
            'suppliers.manage', 'brands.manage', 'generic-names.manage',
            'products.view', 'products.manage', 'purchases.manage', 'stock.adjust',
            'customers.manage', 'organizations.manage', 'contacts.manage',
            'leads.manage', 'activities.manage', 'todos.manage',
            'sales-targets.manage', 'sales.manage', 'sales.view-all', 'sales.return',
            'accounts.manage', 'reports.view', 'performance-reports.view',
        ]);

        $salesExecutive->syncPermissions([
            'dashboard.view',
            'products.view',
            'customers.manage', 'organizations.manage', 'contacts.manage',
            'leads.manage', 'activities.manage', 'todos.manage',
            'sales.manage', 'sales.view-own', 'performance-reports.view',
        ]);

        $marketingExecutive->syncPermissions([
            'dashboard.view',
            'products.view',
            'customers.manage', 'organizations.manage', 'contacts.manage',
            'leads.manage', 'activities.manage', 'todos.manage',
            'events.manage', 'performance-reports.view',
        ]);

        $account->syncPermissions([
            'dashboard.view',
            'products.view', 'purchases.manage', 'suppliers.manage',
            'sales.manage', 'sales.view-all',
            'accounts.manage', 'reports.view', 'performance-reports.view',
        ]);
    }
}
