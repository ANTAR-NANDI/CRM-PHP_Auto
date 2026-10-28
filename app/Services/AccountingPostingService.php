<?php

namespace App\Services;

use App\Models\AccountVoucher;
use App\Models\ChartOfAccount;
use App\Models\CustomerVehicleSale;
use App\Models\GoodsReceipt;
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

    public function postGoodsReceipt(GoodsReceipt $receipt, ?ChartOfAccount $cashAccount, User $user): AccountVoucher
    {
        $supplier = app(PartyAccountService::class)->forSupplier($receipt->supplier);
        $paid = (float) $receipt->paid_amount;
        $due = max(0, (float) $receipt->total_amount - $paid);

        $entries = collect([['account' => $this->account('100010401'), 'debit' => (float) $receipt->total_amount, 'credit' => 0]])
            ->when($paid > 0, fn (Collection $rows) => $rows->push(['account' => $cashAccount, 'debit' => 0, 'credit' => $paid]))
            ->when($due > 0, fn (Collection $rows) => $rows->push(['account' => $supplier, 'debit' => 0, 'credit' => $due]));

        return $this->post('journal', $receipt->mrr_date, "Goods received {$receipt->mrr_no}", $entries, $user);
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

    public function postCustomerVehicleSale(CustomerVehicleSale $sale, User $user): AccountVoucher
    {
        $customer = $sale->customer_id ? $sale->customer : null;
        $paid = max(0, (float) $sale->paid_amount);
        $total = max(0, (float) $sale->final_price);
        $due = max(0, $total - $paid);

        if ($due > 0 && ! $customer) {
            throw new \LogicException('A customer is required when a vehicle sale has an outstanding balance.');
        }

        $entries = collect();
        if ($paid > 0) {
            $entries->push(['account' => $this->vehicleSalePaymentAccount($sale->payment_mode), 'debit' => $paid, 'credit' => 0]);
        }
        if ($due > 0) {
            $entries->push(['account' => app(PartyAccountService::class)->forCustomer($customer), 'debit' => $due, 'credit' => 0]);
        }
        $entries->push(['account' => $this->account('40001'), 'debit' => 0, 'credit' => $total]);

        return $this->post('journal', $sale->sale_date, "Vehicle sale {$sale->sale_no}", $entries, $user);
    }

    public function postCustomerVehicleCollection(CustomerVehicleSale $sale, ChartOfAccount $cashAccount, float $amount, mixed $receivedAt, User $user, string $reference): AccountVoucher
    {
        if (! $sale->customer_id) {
            throw new \LogicException('A customer is required before a collection can be posted.');
        }

        $cashAccount = $this->resolvePostingCashAccount($cashAccount);
        $customer = $sale->customer;
        $entries = collect([
            ['account' => $cashAccount, 'debit' => $amount, 'credit' => 0],
            ['account' => app(PartyAccountService::class)->forCustomer($customer), 'debit' => 0, 'credit' => $amount],
        ]);

        return $this->post('journal', $receivedAt, $reference, $entries, $user);
    }

    public function resolvePostingCashAccount(ChartOfAccount $account): ChartOfAccount
    {
        if ($account->is_transactional) {
            return $account;
        }

        return ChartOfAccount::query()->where('parent_id', $account->id)->where('is_active', true)->where('is_transactional', true)->orderBy('code')->firstOrFail();
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
        app(PartyAccountService::class)->ensureStandardAccounts();

        return ChartOfAccount::query()->where('code', $code)->where('is_active', true)->where('is_transactional', true)->firstOrFail();
    }

    private function cashAccount(string $method): ChartOfAccount
    {
        return $this->account(match ($method) {
            'card' => '1000102', 'mobile_banking', 'mobile' => '1000103', default => '1000101',
        });
    }

    private function vehicleSalePaymentAccount(string $method): ChartOfAccount
    {
        if ($method === 'bank') {
            $bankParent = ChartOfAccount::query()->where('code', '1000102')->firstOrFail();
            return $this->resolvePostingCashAccount($bankParent);
        }

        return $this->account(match ($method) {
            'mobile_banking' => '1000103',
            'cheque' => '1000106',
            default => '1000101',
        });
    }

    private function nextNumber(string $type): string
    {
        return sprintf('JV-%s-%05d', now()->format('Ym'), AccountVoucher::query()->where('voucher_type', $type)->lockForUpdate()->count() + 1);
    }
}
