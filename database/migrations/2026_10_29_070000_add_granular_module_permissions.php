<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        $modules = [
            'activities' => ['Customer Relationship', 'Activity'], 'leads' => ['Customer Relationship', 'Leads'], 'todos' => ['Customer Relationship', 'To Do List'], 'contacts' => ['Customer Relationship', 'Contact'], 'organizations' => ['Customer Relationship', 'Organization'],
            'events' => ['Promotion & Campaign', 'Events'], 'budgets' => ['Promotion & Campaign', 'Budgets'], 'bulk-sms' => ['Promotion & Campaign', 'Bulk SMS'], 'pre-event-plans' => ['Meeting & Planning', 'Pre-event Plans'], 'agendas' => ['Meeting & Planning', 'Agendas'], 'meetings' => ['Meeting & Planning', 'Meetings'],
            'sales-targets' => ['Order & Sale Target', 'Sales Target'], 'sales' => ['Order & Sale Target', 'Sales'], 'products' => ['Item Inventory', 'Items'], 'purchases' => ['Item Inventory', 'Receive / Transfer'], 'suppliers' => ['Item Inventory', 'Supplier'], 'brands' => ['Stock & Inventory', 'Brands'],
            'customers' => ['Customer Relationship', 'Customer'], 'accounts' => ['Accounts', 'Accounts'], 'employees' => ['Config', 'Employee'], 'config.roles' => ['Config', 'User Role'], 'config.stores' => ['System Settings', 'Showroom'], 'system-settings' => ['System Settings', 'Master Data'],
        ];
        foreach ($modules as $key => [$module, $submodule]) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $permission = Permission::findOrCreate("{$key}.{$action}", 'web');
                DB::table('permission_definitions')->updateOrInsert(['permission_id' => $permission->id], ['module_name' => $module, 'submodule_name' => $submodule, 'created_at' => now(), 'updated_at' => now()]);
            }
            $legacy = str_contains($key, 'config.') ? "{$key}.manage" : "{$key}.manage";
            $legacyPermission = Permission::query()->where('name', $legacy)->where('guard_name', 'web')->first();
            if ($legacyPermission) {
                $roles = Role::query()->whereHas('permissions', fn ($query) => $query->whereKey($legacyPermission->id))->get();
                $newPermissions = Permission::query()->whereIn('name', ["{$key}.view", "{$key}.add", "{$key}.edit", "{$key}.delete"])->get();
                foreach ($roles as $role) $role->givePermissionTo($newPermissions);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void { Permission::query()->whereIn('name', Permission::query()->where('name', 'like', '%.view')->orWhere('name', 'like', '%.add')->orWhere('name', 'like', '%.edit')->orWhere('name', 'like', '%.delete')->pluck('name'))->delete(); app(PermissionRegistrar::class)->forgetCachedPermissions(); }
};
