<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions.index', [
            'permissions' => Permission::query()->leftJoin('permission_definitions as definition', 'permissions.id', '=', 'definition.permission_id')
                ->where('permissions.guard_name', 'web')
                ->withCount('roles')
                ->select('permissions.*', 'definition.module_name', 'definition.submodule_name')
                ->orderBy('permissions.name')
                ->paginate(50),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        $this->saveDefinition($permission, $data);
        $this->clearPermissionCache();

        return redirect()->route('permissions.index')->with('success', 'Permission added successfully.');
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $this->ensureWebPermission($permission);
        $data = $this->validated($request, $permission);
        $permission->update(['name' => $data['name']]);
        $this->saveDefinition($permission, $data);
        $this->clearPermissionCache();

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->ensureWebPermission($permission);

        if ($permission->roles()->exists()) {
            return back()->with('error', 'Remove this permission from its roles before deleting it.');
        }

        $permission->delete();
        $this->clearPermissionCache();

        return back()->with('success', 'Permission deleted successfully.');
    }

    private function validated(Request $request, ?Permission $permission = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9.-]+$/', Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission)],
            'module_name' => ['required', 'string', 'max:100'],
            'submodule_name' => ['required', 'string', 'max:100'],
        ]);
    }

    private function ensureWebPermission(Permission $permission): void
    {
        abort_unless($permission->guard_name === 'web', 404);
    }

    private function clearPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function saveDefinition(Permission $permission, array $data): void
    {
        DB::table('permission_definitions')->updateOrInsert(
            ['permission_id' => $permission->id],
            ['module_name' => $data['module_name'], 'submodule_name' => $data['submodule_name'], 'updated_at' => now(), 'created_at' => now()],
        );
    }
}
