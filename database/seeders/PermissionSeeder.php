<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'dashboard.view', 'employees.manage',
            'config.roles.manage', 'config.employees.manage', 'config.users.manage', 'config.stores.manage', 'config.store-positions.manage',
            'system-settings.manage',
            'suppliers.manage', 'brands.manage', 'generic-names.manage',
            'products.view', 'products.manage', 'purchases.manage', 'customers.manage', 'stock.adjust',
            'pos.access', 'sales.view-own', 'sales.view-all', 'sales.return',
            'reports.view', 'accounts.manage', 'settings.manage', 'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view', 'events.manage', 'budgets.manage', 'bulk-sms.manage', 'pre-event-plans.manage', 'agendas.manage', 'meetings.manage', 'sales-targets.manage', 'sales.manage',
        ] as $name) {
            $permission = Permission::findOrCreate($name, 'web');
            [$module, $submodule] = $this->definition($name);
            DB::table('permission_definitions')->updateOrInsert(
                ['permission_id' => $permission->id],
                ['module_name' => $module, 'submodule_name' => $submodule, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    private function definition(string $name): array
    {
        return match ($name) {
            'config.roles.manage' => ['Configuration', 'Roles'],
            'config.employees.manage', 'employees.manage' => ['Configuration', 'Employees'],
            'config.users.manage' => ['Configuration', 'Users'],
            'config.stores.manage' => ['Configuration', 'Stores'],
            'config.store-positions.manage' => ['Configuration', 'Store Positions'],
            'system-settings.manage', 'settings.manage' => ['System Settings', 'Master Data'],
            'dashboard.view' => ['Dashboard', 'Overview'],
            'activities.manage', 'activity-setup.manage', 'leads.manage', 'todos.manage', 'contacts.manage', 'organizations.manage', 'performance-reports.view' => ['Customer Relationship', ucfirst(str_replace(['.manage', '.view', '-'], ['', '', ' '], $name))],
            'events.manage', 'budgets.manage', 'bulk-sms.manage' => ['Promotion & Campaign', ucfirst(str_replace(['.manage', '-'], ['', ' '], $name))],
            'pre-event-plans.manage', 'agendas.manage', 'meetings.manage' => ['Meeting & Planning', ucfirst(str_replace(['.manage', '-'], ['', ' '], $name))],
            'sales-targets.manage', 'sales.manage', 'sales.view-own', 'sales.view-all', 'sales.return' => ['Sales', ucfirst(str_replace(['.manage', '.view-own', '.view-all', '.return', '-'], ['', '', '', '', ' '], $name))],
            'suppliers.manage', 'brands.manage', 'generic-names.manage', 'products.view', 'products.manage', 'stock.adjust' => ['Inventory', ucfirst(str_replace(['.manage', '.view', '.adjust', '-'], ['', '', '', ' '], $name))],
            'purchases.manage', 'pos.access' => ['Sales & Operations', $name === 'pos.access' ? 'Point of Sale' : 'Purchases'],
            'reports.view' => ['Reports', 'Reports'],
            'accounts.manage' => ['Accounts', 'Accounts'],
            default => ['Other', 'General'],
        };
    }
}
