<?php

namespace App\Services;

use App\Models\AccountVoucher;
use App\Models\ChartOfAccount;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;

class AccountingPostingService
{
    public function postPurchase(Purchase $purchase, User $user): AccountVoucher
    {
        $supplier = app(PartyAccountService::class)->forSupplier($purchase->supplier);
        $entries = collect([['account' => $this->account('100010401'), 'debit' => (float) $purchase->total, 'credit' => 0]])
            ->when((float) $purchase->paid > 0, fn (Collection $rows) => $rows->push(['account' => $this->account('1000101'), 'debit' => 0, 'credit' => (float) $purchase->paid]))
            ->when((float) $purchase->total - (float) $purchase->paid > 0, fn (Collection $rows) => $rows->push(['account' => $supplier, 'debit' => 0, 'credit' => round((float) $purchase->total - (float) $purchase->paid, 2)]));

        return $this->post('journal', $purchase->purchased_at, "Purchase {$purchase->invoice_number}", $entries, $user);
    }

    public function postSale(Sale $sale, float $cost, User $user): AccountVoucher
    {
        $entries = collect();
        if ((float) $sale->paid > 0) {
            $entries->push(['account' => $this->cashAccount($sale->payment_method), 'debit' => (float) $sale->paid, 'credit' => 0]);
        }
        if ((float) $sale->due > 0 && $sale->customer) {
            $entries->push(['account' => app(PartyAccountService::class)->forCustomer($sale->customer), 'debit' => (float) $sale->due, 'credit' => 0]);
        }
        $entries->push(['account' => $this->account('40001'), 'debit' => 0, 'credit' => (float) $sale->total]);
        if ($cost > 0) {
            $entries->push(['account' => $this->account('50001'), 'debit' => $cost, 'credit' => 0]);
            $entries->push(['account' => $this->account('100010401'), 'debit' => 0, 'credit' => $cost]);
        }

        return $this->post('journal', $sale->sold_at, "Sale {$sale->invoice_number}", $entries, $user);
    }

    private function post(string $type, mixed $date, string $narration, Collection $entries, User $user): AccountVoucher
    {
        $amount = round((float) $entries->sum('debit'), 2);
        $voucher = AccountVoucher::create(['voucher_number' => $this->nextNumber($type), 'voucher_type' => $type, 'voucher_date' => $date, 'narration' => $narration, 'amount' => $amount, 'user_id' => $user->id]);
        $voucher->entries()->createMany($entries->filter(fn (array $entry) => $entry['debit'] > 0 || $entry['credit'] > 0)->map(fn (array $entry) => ['chart_of_account_id' => $entry['account']->id, 'debit' => $entry['debit'], 'credit' => $entry['credit']])->all());

        return $voucher;
    }

    private function account(string $code): ChartOfAccount
    {
        return ChartOfAccount::query()->where('code', $code)->where('is_active', true)->where('is_transactional', true)->firstOrFail();
    }

    private function cashAccount(string $method): ChartOfAccount
    {
        return $this->account(match ($method) {
            'card' => '1000102', 'mobile_banking', 'mobile' => '1000103', default => '1000101',
        });
    }

    private function nextNumber(string $type): string
    {
        return sprintf('JV-%s-%05d', now()->format('Ym'), AccountVoucher::query()->where('voucher_type', $type)->lockForUpdate()->count() + 1);
    }
}
