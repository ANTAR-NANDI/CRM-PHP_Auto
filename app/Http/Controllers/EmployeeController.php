<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PartyAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $employees = User::query()->with('roles')
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.employees.index', compact('employees', 'search'));
    }

    public function create(): View
    {
        return view('admin.employees.create', ['roles' => Role::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $employee = User::create($data['employee']);
            $employee->update(['employee_code' => 'EMP-'.str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT)]);
            $employee->syncRoles([$data['role']]);
            app(PartyAccountService::class)->forEmployee($employee);
        });

        return redirect()->route('employees.index')->with('success', 'Employee added and role assigned successfully.');
    }

    public function edit(User $employee): View
    {
        return view('admin.employees.edit', [
            'employee' => $employee,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $data = $this->validated($request, $employee);

        if ($request->user()->is($employee) && (! $data['employee']['is_active'] || ! $employee->hasRole($data['role']))) {
            throw ValidationException::withMessages(['role' => 'You cannot deactivate your own account or change your own role.']);
        }

        DB::transaction(function () use ($data, $employee) {
            $employee->update($data['employee']);
            $employee->syncRoles([$data['role']]);
            app(PartyAccountService::class)->forEmployee($employee);
        });

        return redirect()->route('employees.index')->with('success', 'Employee information updated successfully.');
    }

    private function validated(Request $request, ?User $employee = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($employee)],
            'phone' => ['nullable', 'string', 'max:50'],
            'designation' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'address' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => [$employee ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $userData = collect($validated)->only(['name', 'email', 'phone', 'designation', 'joining_date', 'salary', 'address', 'password'])->toArray();
        if (empty($userData['password'])) {
            unset($userData['password']);
        }
        $userData['role'] = $validated['role'];
        $userData['is_active'] = $request->boolean('is_active');

        return ['employee' => $userData, 'role' => $validated['role']];
    }
}
