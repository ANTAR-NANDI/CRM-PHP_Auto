<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['100', 'Assets', null, 'asset', false], ['10001', 'Current Assets', '100', 'asset', false],
            ['1000101', 'Cash in Hand', '10001', 'asset', true], ['1000102', 'Cash at Bank', '10001', 'asset', true],
            ['1000103', 'Mobile Financial Services', '10001', 'asset', true], ['1000104', 'Medicine Inventory', '10001', 'asset', false],
            ['100010401', 'Finished Medicines', '1000104', 'asset', true], ['1000105', 'Customer Receivable', '10001', 'asset', false],
            ['200', 'Liabilities', null, 'liability', false], ['20001', 'Current Liabilities', '200', 'liability', false],
            ['2000101', 'Supplier Payable', '20001', 'liability', false], ['2000102', 'Employee Payable', '20001', 'liability', false], ['300', 'Equity', null, 'equity', false],
            ['30001', 'Owner Capital', '300', 'equity', true], ['400', 'Income', null, 'income', false],
            ['40001', 'Sales Revenue', '400', 'income', true], ['40002', 'Other Income', '400', 'income', true],
            ['500', 'Expenses', null, 'expense', false], ['50001', 'Cost of Goods Sold', '500', 'expense', true],
            ['50002', 'Operating Expenses', '500', 'expense', false], ['5000201', 'Shop Rent', '50002', 'expense', true],
            ['5000202', 'Salary Expense', '50002', 'expense', true], ['5000203', 'Electricity & Utility', '50002', 'expense', true],
            ['5000204', 'Marketing Expense', '50002', 'expense', true], ['5000205', 'Miscellaneous Expense', '50002', 'expense', true],
        ];

        foreach ($accounts as [$code, $name, $parentCode, $type, $transactional]) {
            $parent = $parentCode ? ChartOfAccount::query()->where('code', $parentCode)->first() : null;
            ChartOfAccount::updateOrCreate(['code' => $code], ['name' => $name, 'parent_id' => $parent?->id, 'account_type' => $type, 'level' => $parent ? $parent->level + 1 : 1, 'is_transactional' => $transactional, 'is_active' => true]);
        }
    }
}
