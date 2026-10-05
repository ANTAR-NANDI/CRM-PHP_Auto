<?php

namespace App\Http\Controllers;

use App\Models\AccountOpeningBalance;
use App\Models\Customer;
use App\Models\PartyTransaction;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PartyLedgerController extends Controller
{
    public function customer(Request $request): View
    {
        $data = $this->filters($request, 'customer_id', 'customers');
        $party = isset($data['customer_id']) ? Customer::query()->findOrFail($data['customer_id']) : null;

        return view('admin.ledgers.statement', $this->statementData('customer', $party, $data));
    }

    public function supplier(Request $request): View
    {
        $data = $this->filters($request, 'supplier_id', 'suppliers');
        $party = isset($data['supplier_id']) ? Supplier::query()->findOrFail($data['supplier_id']) : null;

        return view('admin.ledgers.statement', $this->statementData('supplier', $party, $data));
    }

    private function filters(Request $request, string $key, string $table): array
    {
        $data = $request->validate([
            $key => ['nullable', 'integer', "exists:{$table},id"],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [...$data, 'from' => isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : today()->startOfMonth(), 'to' => isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : today()->endOfDay()];
    }

    private function statementData(string $type, Customer|Supplier|null $party, array $data): array
    {
        $parties = $type === 'customer' ? Customer::query()->orderBy('name')->get() : Supplier::query()->orderBy('name')->get();
        $from = $data['from'];
        $to = $data['to'];
        $rows = collect();
        $opening = 0.0;

        if ($party) {
            $opening = $this->openingBalance($type, $party, $from);
            $rows = $type === 'customer' ? $this->customerRows($party, $from, $to) : $this->supplierRows($party, $from, $to);
        }

        $balance = $opening;
        $rows = $rows->sortBy(fn (array $row) => $row['date'].'-'.$row['sort'])->values()->map(function (array $row) use (&$balance, $type) {
            $balance += $type === 'customer' ? $row['debit'] - $row['credit'] : $row['credit'] - $row['debit'];
            return [...$row, 'balance' => round($balance, 2)];
        });

        return compact('type', 'party', 'parties', 'from', 'to', 'opening', 'rows') + ['closing' => round($balance, 2)];
    }

    private function openingBalance(string $type, Customer|Supplier $party, Carbon $from): float
    {
        $accountOpening = 0.0;
        if ($party->chart_of_account_id) {
            $opening = AccountOpeningBalance::query()->where('chart_of_account_id', $party->chart_of_account_id)->whereDate('opening_date', '<', $from)->first();
            $accountOpening = $opening ? ((float) $opening->debit - (float) $opening->credit) : 0.0;
        }

        if ($type === 'customer') {
            $invoices = (float) Sale::query()->where('customer_id', $party->id)->where('customer_type', '!=', 'walking')->where('sold_at', '<', $from)->sum('total');
            $payments = (float) PartyTransaction::query()->where('customer_id', $party->id)->where('transaction_type', 'customer_receive')->where('transaction_date', '<', $from)->sum('amount');
            return $accountOpening + $invoices - $payments;
        }

        $purchases = (float) Purchase::query()->where('supplier_id', $party->id)->where('purchased_at', '<', $from)->sum('total');
        $payments = (float) PartyTransaction::query()->where('supplier_id', $party->id)->where('transaction_type', 'supplier_payment')->where('transaction_date', '<', $from)->sum('amount');
        return -$accountOpening + $purchases - $payments;
    }

    private function customerRows(Customer $customer, Carbon $from, Carbon $to): Collection
    {
        $sales = Sale::query()->where('customer_id', $customer->id)->whereBetween('sold_at', [$from, $to])->get()->map(fn (Sale $sale) => ['date' => $sale->sold_at->toDateString(), 'sort' => '1-'.$sale->id, 'reference' => $sale->invoice_number, 'description' => 'Medicine sale invoice', 'debit' => (float) $sale->total, 'credit' => 0.0, 'url' => route('sales.show', $sale)]);
        $payments = PartyTransaction::query()->where('customer_id', $customer->id)->where('transaction_type', 'customer_receive')->whereBetween('transaction_date', [$from, $to])->get()->map(fn (PartyTransaction $row) => ['date' => $row->transaction_date->toDateString(), 'sort' => '2-'.$row->id, 'reference' => $row->transaction_number, 'description' => 'Customer payment'.($row->narration ? ': '.$row->narration : ''), 'debit' => 0.0, 'credit' => (float) $row->amount, 'url' => route('party-transactions.show', $row)]);

        return $sales->concat($payments);
    }

    private function supplierRows(Supplier $supplier, Carbon $from, Carbon $to): Collection
    {
        $purchases = Purchase::query()->where('supplier_id', $supplier->id)->whereBetween('purchased_at', [$from->toDateString(), $to->toDateString()])->get()->map(fn (Purchase $purchase) => ['date' => $purchase->purchased_at->toDateString(), 'sort' => '1-'.$purchase->id, 'reference' => $purchase->invoice_number, 'description' => 'Medicine purchase invoice', 'debit' => 0.0, 'credit' => (float) $purchase->total, 'url' => route('purchases.show', $purchase)]);
        $payments = PartyTransaction::query()->where('supplier_id', $supplier->id)->where('transaction_type', 'supplier_payment')->whereBetween('transaction_date', [$from, $to])->get()->map(fn (PartyTransaction $row) => ['date' => $row->transaction_date->toDateString(), 'sort' => '2-'.$row->id, 'reference' => $row->transaction_number, 'description' => 'Supplier payment'.($row->narration ? ': '.$row->narration : ''), 'debit' => (float) $row->amount, 'credit' => 0.0, 'url' => route('party-transactions.show', $row)]);

        return $purchases->concat($payments);
    }
}
