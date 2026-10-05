<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\PartyTransaction;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\PartyAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PartyTransactionController extends Controller
{
    public function index(string $type): View
    {
        $this->ensureType($type);
        $transactions = PartyTransaction::query()->with(['customer', 'supplier', 'cashAccount', 'creator'])->where('transaction_type', $type)->latest('transaction_date')->latest('id')->paginate(20);

        return view('admin.party-transactions.index', compact('transactions', 'type'));
    }

    public function create(string $type): View
    {
        $this->ensureType($type);
        $cashAccounts = ChartOfAccount::query()->where('is_active', true)->where('is_transactional', true)->where('account_type', 'asset')->orderBy('code')->get();
        $customers = Customer::query()->where('is_active', true)->where('due_balance', '>', 0)->orderBy('name')->get();
        // List every supplier so inactive/master-data issues are visible instead
        // of silently leaving the payment dropdown empty.
        $suppliers = Supplier::query()->orderBy('name')->get();
        // Use the Purchase model due accessor so this screen always matches the
        // Purchase Invoice's Total − Paid = Due calculation.
        $supplierDues = Purchase::query()
            ->get(['supplier_id', 'total', 'paid'])
            ->groupBy('supplier_id')
            ->map(fn ($purchases) => $purchases->sum(fn (Purchase $purchase) => (float) $purchase->due));

        return view('admin.party-transactions.create', compact('type', 'cashAccounts', 'customers', 'suppliers', 'supplierDues'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $this->ensureType($type);
        $data = $request->validate([
            'transaction_date' => ['required', 'date'],
            'customer_id' => [$type === 'customer_receive' ? 'required' : 'nullable', 'integer', 'exists:customers,id'],
            'supplier_id' => [$type === 'supplier_payment' ? 'required' : 'nullable', 'integer', 'exists:suppliers,id'],
            'cash_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'narration' => ['nullable', 'string', 'max:1000'],
        ]);

        $cash = ChartOfAccount::query()->whereKey($data['cash_account_id'])->where('is_active', true)->where('is_transactional', true)->where('account_type', 'asset')->firstOrFail();
        $transaction = DB::transaction(function () use ($data, $type, $cash, $request): PartyTransaction {
            $amount = (float) $data['amount'];
            if ($type === 'customer_receive') {
                $party = Customer::query()->lockForUpdate()->findOrFail($data['customer_id']);
                if ($amount > (float) $party->due_balance + 0.001) {
                    throw ValidationException::withMessages(['amount' => 'Received amount cannot exceed the customer due balance.']);
                }
                $this->allocateCustomerReceive($party, $amount);
                $voucherType = 'credit';
                $debitAccount = $cash->id;
                $creditAccount = app(PartyAccountService::class)->forCustomer($party)->id;
                $numberPrefix = 'CR';
            } else {
                $party = Supplier::query()->lockForUpdate()->findOrFail($data['supplier_id']);
                $due = (float) Purchase::query()
                    ->where('supplier_id', $party->id)
                    ->get(['total', 'paid'])
                    ->sum(fn (Purchase $purchase) => (float) $purchase->due);
                if ($amount > $due + 0.001) {
                    throw ValidationException::withMessages(['amount' => 'Payment amount cannot exceed the supplier outstanding balance.']);
                }
                $this->allocateSupplierPayment($party, $amount);
                $voucherType = 'debit';
                $debitAccount = app(PartyAccountService::class)->forSupplier($party)->id;
                $creditAccount = $cash->id;
                $numberPrefix = 'SP';
            }

            $voucher = AccountVoucher::create(['voucher_number' => $this->nextVoucherNumber($voucherType), 'voucher_type' => $voucherType, 'voucher_date' => $data['transaction_date'], 'narration' => blank($data['narration'] ?? null) ? null : $data['narration'], 'amount' => $amount, 'user_id' => $request->user()->id]);
            $voucher->entries()->createMany([
                ['chart_of_account_id' => $debitAccount, 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $creditAccount, 'debit' => 0, 'credit' => $amount],
            ]);

            return PartyTransaction::create(['transaction_number' => $this->nextNumber($numberPrefix), 'transaction_type' => $type, 'customer_id' => $type === 'customer_receive' ? $party->id : null, 'supplier_id' => $type === 'supplier_payment' ? $party->id : null, 'cash_account_id' => $cash->id, 'account_voucher_id' => $voucher->id, 'transaction_date' => $data['transaction_date'], 'amount' => $amount, 'narration' => blank($data['narration'] ?? null) ? null : $data['narration'], 'user_id' => $request->user()->id]);
        });

        return redirect()->route('party-transactions.show', $transaction)->with('success', 'Transaction saved successfully.');
    }

    public function show(PartyTransaction $transaction): View
    {
        $transaction->load(['customer', 'supplier', 'cashAccount', 'voucher.entries.account', 'creator']);

        return view('admin.party-transactions.show', compact('transaction'));
    }

    private function allocateCustomerReceive(Customer $customer, float $amount): void
    {
        $remaining = $amount;
        foreach (Sale::query()->where('customer_id', $customer->id)->where('due', '>', 0)->orderBy('sold_at')->lockForUpdate()->get() as $sale) {
            $applied = min($remaining, (float) $sale->due);
            $sale->increment('paid', $applied);
            $sale->decrement('due', $applied);
            $remaining -= $applied;
            if ($remaining < 0.001) break;
        }
        $customer->decrement('due_balance', $amount);
    }

    private function allocateSupplierPayment(Supplier $supplier, float $amount): void
    {
        $remaining = $amount;
        foreach (Purchase::query()->where('supplier_id', $supplier->id)->orderBy('purchased_at')->lockForUpdate()->get() as $purchase) {
            if ((float) $purchase->due <= 0) {
                continue;
            }
            $applied = min($remaining, (float) $purchase->total - (float) $purchase->paid);
            $purchase->increment('paid', $applied);
            $remaining -= $applied;
            if ($remaining < 0.001) break;
        }
    }

    private function ensureType(string $type): void { abort_unless(in_array($type, ['customer_receive', 'supplier_payment'], true), 404); }
    private function nextNumber(string $prefix): string { return sprintf('%s-%s-%05d', $prefix, now()->format('Ym'), PartyTransaction::query()->where('transaction_type', $prefix === 'CR' ? 'customer_receive' : 'supplier_payment')->lockForUpdate()->count() + 1); }
    private function nextVoucherNumber(string $type): string { $prefix = $type === 'credit' ? 'CV' : 'DV'; return sprintf('%s-%s-%05d', $prefix, now()->format('Ym'), AccountVoucher::query()->where('voucher_type', $type)->lockForUpdate()->count() + 1); }
}
