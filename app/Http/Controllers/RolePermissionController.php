<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    private const PROTECTED_ROLES = ['admin', 'manager', 'cashier', 'salesperson'];

    public function index(): View
    {
        $roles = Role::query()->where('guard_name', 'web')->withCount(['permissions', 'users'])->orderBy('name')->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', ['role' => new Role(), 'permissions' => $this->permissionsByModule(), 'selectedPermissions' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);

        return redirect()->route('roles.index')->with('success', 'Role created and permissions assigned successfully.');
    }

    public function edit(Role $role): View
    {
        $this->ensureWebRole($role);

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->permissionsByModule(),
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureWebRole($role);
        $data = $this->validated($request, $role);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return redirect()->route('roles.index')->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureWebRole($role);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->with('error', 'Built-in roles cannot be deleted. You may edit their permissions.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Move employees to another role before deleting this role.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('roles', 'name')->ignore($role)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        $data['permissions'] = $data['permissions'] ?? [];

        return $data;
    }

    private function permissionsByModule(): array
    {
        $modules = [
            'config.roles.manage' => ['Config', 'User Role'],
            'config.employees.manage' => ['Config', 'Employee'],
            'config.users.manage' => ['Config', 'New User / User'],
            'config.stores.manage' => ['Config', 'Store'],
            'config.store-positions.manage' => ['Config', 'Store Position'],
            'system-settings.manage' => ['System Settings', 'Master Data'],
            'dashboard' => ['Dashboard', 'Overview'],
            'activities' => ['Customer Relationship', 'Activities'],
            'activity-setup' => ['Customer Relationship', 'Activity Setup'],
            'bulk-sms' => ['Promotion & Campaign', 'Bulk SMS'],
            'pre-event-plans' => ['Meeting & Planning', 'Pre-Event Plans'],
            'agendas' => ['Meeting & Planning', 'Agendas'],
            'meetings' => ['Meeting & Planning', 'Meetings'],
            'sales-targets' => ['Order & Sales', 'Sales Targets'],
            'sales' => ['Order & Sales', 'Customer Sales'],
            'leads' => ['Customer Relationship', 'Leads'],
            'todos' => ['Customer Relationship', 'Task To-Do'],
            'contacts' => ['Customer Relationship', 'Contacts'],
            'organizations' => ['Customer Relationship', 'Organizations'],
            'performance-reports' => ['Customer Relationship', 'Performance Report'],
            'customers' => ['Customer Relationship', 'Customers'],
            'suppliers' => ['Medicine & Inventory', 'Suppliers'],
            'brands' => ['Medicine & Inventory', 'Brands'],
            'generic-names' => ['Medicine & Inventory', 'Generic Names'],
            'products' => ['Medicine & Inventory', 'Products'],
            'stock' => ['Medicine & Inventory', 'Stock'],
            'purchases' => ['Sales & Operations', 'Purchases'],
            'pos' => ['Sales & Operations', 'Point of Sale'],
            'sales' => ['Sales & Operations', 'Sales'],
            'reports' => ['Reports', 'Reports'],
            'accounts' => ['Accounts', 'Accounts'],
            'employees' => ['Human Resources', 'Employees'],
            'settings' => ['System Settings', 'Roles & Permissions'],
        ];

        $grouped = [];
        Permission::query()->where('guard_name', 'web')->orderBy('name')->get()->each(function (Permission $permission) use (&$grouped, $modules) {
            [$module, $subModule] = $modules[$permission->name] ?? $modules[explode('.', $permission->name)[0]] ?? ['Other', 'General'];
            $grouped[$module][$subModule][] = $permission;
        });

        return $grouped;
    }

    private function ensureWebRole(Role $role): void
    {
        abort_unless($role->guard_name === 'web', 404);
    }
}
