<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_employee_and_assign_role(): void
    {
        $admin = $this->userWithRole('admin');
        Role::create(['name' => 'cashier', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'name' => 'POS Cashier',
            'email' => 'pos@example.com',
            'phone' => '01700000000',
            'designation' => 'Cashier',
            'joining_date' => today()->toDateString(),
            'salary' => 18000,
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $employee = User::query()->where('email', 'pos@example.com')->firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('employees.index'));
        $this->assertSame('EMP-'.str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT), $employee->employee_code);
        $this->assertTrue($employee->hasRole('cashier'));
        $this->assertSame('18000.00', $employee->salary);
    }

    public function test_salesperson_cannot_manage_employee_accounts(): void
    {
        $manager = $this->userWithRole('salesperson');

        $this->actingAs($manager)->get(route('employees.index'))->assertForbidden();
    }

    public function test_admin_can_record_daily_attendance(): void
    {
        $manager = $this->userWithRole('admin');
        $cashier = $this->userWithRole('cashier');

        $response = $this->actingAs($manager)->post(route('attendance.store'), [
            'attendance_date' => today()->toDateString(),
            'employees' => [
                $cashier->id => [
                    'status' => 'present',
                    'check_in' => '09:00',
                    'check_out' => '18:00',
                    'notes' => 'Regular shift',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('attendance.index', ['date' => today()->toDateString()]));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $cashier->id,
            'status' => 'present',
            'recorded_by' => $manager->id,
        ]);
        $this->assertSame(1, Attendance::query()->count());
    }

    public function test_inactive_employee_cannot_log_in(): void
    {
        $employee = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $employee->email, 'password' => 'password']);

        $this->assertGuest();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }
}
