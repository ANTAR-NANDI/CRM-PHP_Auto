<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;

class PartyAccountService
{
    public function forCustomer(Customer $customer): ChartOfAccount
    {
        return $this->forParty($customer, '1000105', 'Customer', 'asset', 'C');
    }

    public function forSupplier(Supplier $supplier): ChartOfAccount
    {
        return $this->forParty($supplier, '2000101', 'Supplier', 'liability', 'S');
    }

    public function forEmployee(User $employee): ChartOfAccount
    {
        return $this->forParty($employee, '2000102', 'Employee', 'liability', 'E');
    }

    private function forParty(Customer|Supplier|User $party, string $parentCode, string $label, string $type, string $prefix): ChartOfAccount
    {
        $parent = ChartOfAccount::query()->where('code', $parentCode)->firstOrFail();
        $code = $parentCode.'-'.$prefix.str_pad((string) $party->id, 5, '0', STR_PAD_LEFT);
        $account = ChartOfAccount::query()->updateOrCreate(
            ['code' => $code],
            ['name' => $label.': '.Str::limit($party->name, 150), 'parent_id' => $parent->id, 'account_type' => $type, 'level' => $parent->level + 1, 'is_transactional' => true, 'is_active' => true],
        );

        if ($party->chart_of_account_id !== $account->id) {
            $party->forceFill(['chart_of_account_id' => $account->id])->saveQuietly();
        }

        return $account;
    }
}
