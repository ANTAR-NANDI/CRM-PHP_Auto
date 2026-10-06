<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Sales', 'SALES'], ['Marketing', 'MKT'], ['Operations', 'OPS'], ['Accounts', 'ACC'], ['Human Resources', 'HR']] as [$name, $code]) {
            DB::table('departments')->updateOrInsert(['name' => $name], ['code' => $code, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
