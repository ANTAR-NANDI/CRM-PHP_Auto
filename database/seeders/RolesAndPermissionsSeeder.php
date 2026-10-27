<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $manager = Role::findOrCreate('manager');
        $salesExecutive = Role::findOrCreate('sales-executive');

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
    }
}
