<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManagementAndPrEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Management Employee', 'email' => 'management@w3crm.local', 'employee_code' => 'EMP-MGMT', 'designation' => 'Management', 'role' => 'management'],
            ['name' => 'PR Employee', 'email' => 'pr@w3crm.local', 'employee_code' => 'EMP-PR', 'designation' => 'Public Relations', 'role' => 'pr'],
        ] as $employee) {
            $user = User::query()->updateOrCreate(['email' => $employee['email']], $employee + ['password' => Hash::make('ChangeMe!2026'), 'is_active' => true, 'email_verified_at' => now()]);
            $user->syncRoles([Role::findByName($employee['role'], 'web')]);
        }
    }
}
