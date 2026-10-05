<?php

namespace App\Http\Controllers;

use App\Models\AccountOpeningBalance;
use App\Models\AccountVoucherEntry;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function profitLoss(Request $request): View
    {
        [$from, $to] = $this->dates($request);
        $totals = AccountVoucherEntry::query()->selectRaw('chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.name, chart_of_accounts.account_type, COALESCE(SUM(account_voucher_entries.debit), 0) AS debit, COALESCE(SUM(account_voucher_entries.credit), 0) AS credit')
            ->join('account_vouchers', 'account_voucher_entries.account_voucher_id', '=', 'account_vouchers.id')
            ->join('chart_of_accounts', 'account_voucher_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->whereBetween('account_vouchers.voucher_date', [$from, $to])
            ->whereIn('chart_of_accounts.account_type', ['income', 'expense'])
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.account_type')
            ->orderBy('chart_of_accounts.code')->get();
        $income = $totals->where('account_type', 'income')->map(fn ($row) => (object) [...(array) $row, 'amount' => (float) $row->credit - (float) $row->debit]);
        $expenses = $totals->where('account_type', 'expense')->map(fn ($row) => (object) [...(array) $row, 'amount' => (float) $row->debit - (float) $row->credit]);
        $totalIncome = $income->sum('amount');
        $totalExpenses = $expenses->sum('amount');

        return view('admin.reports.profit-loss', compact('from', 'to', 'income', 'expenses', 'totalIncome', 'totalExpenses'));
    }

    public function trialBalance(Request $request): View
    {
        $asOf = Carbon::parse($request->validate(['as_of' => ['nullable', 'date']])['as_of'] ?? today());
        $opening = AccountOpeningBalance::query()->whereDate('opening_date', '<=', $asOf)->selectRaw('chart_of_account_id, SUM(debit) AS debit, SUM(credit) AS credit')->groupBy('chart_of_account_id')->get()->keyBy('chart_of_account_id');
        $movement = AccountVoucherEntry::query()->selectRaw('chart_of_account_id, SUM(debit) AS debit, SUM(credit) AS credit')->join('account_vouchers', 'account_voucher_entries.account_voucher_id', '=', 'account_vouchers.id')->whereDate('account_vouchers.voucher_date', '<=', $asOf)->groupBy('chart_of_account_id')->get()->keyBy('chart_of_account_id');
        $accounts = ChartOfAccount::query()->where('is_transactional', true)->orderBy('code')->get()->map(function (ChartOfAccount $account) use ($opening, $movement) {
            $net = (float) ($opening[$account->id]->debit ?? 0) - (float) ($opening[$account->id]->credit ?? 0) + (float) ($movement[$account->id]->debit ?? 0) - (float) ($movement[$account->id]->credit ?? 0);
            return (object) ['code' => $account->code, 'name' => $account->name, 'debit' => max(0, $net), 'credit' => max(0, -$net)];
        })->filter(fn ($row) => $row->debit > 0 || $row->credit > 0)->values();

        return view('admin.reports.trial-balance', ['asOf' => $asOf, 'accounts' => $accounts, 'totalDebit' => $accounts->sum('debit'), 'totalCredit' => $accounts->sum('credit')]);
    }

    public function incomeStatement(Request $request): View
    {
        [$from, $to] = $this->dates($request);
        $data = $this->incomeExpense($from, $to);

        return view('admin.reports.income-statement', compact('from', 'to') + $data);
    }

    public function balanceSheet(Request $request): View
    {
        $asOf = Carbon::parse($request->validate(['as_of' => ['nullable', 'date']])['as_of'] ?? today());
        $balances = $this->accountBalances($asOf);
        $assets = $balances->where('account_type', 'asset')->map(fn ($row) => (object) [...(array) $row, 'amount' => (float) $row->net]);
        $liabilities = $balances->where('account_type', 'liability')->map(fn ($row) => (object) [...(array) $row, 'amount' => -(float) $row->net]);
        $equity = $balances->where('account_type', 'equity')->map(fn ($row) => (object) [...(array) $row, 'amount' => -(float) $row->net]);
        $incomeExpense = $this->incomeExpense(Carbon::create(2000, 1, 1), $asOf);
        $currentEarnings = $incomeExpense['totalIncome'] - $incomeExpense['totalExpenses'];

        return view('admin.reports.balance-sheet', ['asOf' => $asOf, 'assets' => $assets, 'liabilities' => $liabilities, 'equity' => $equity, 'currentEarnings' => $currentEarnings, 'totalAssets' => $assets->sum('amount'), 'totalLiabilities' => $liabilities->sum('amount'), 'totalEquity' => $equity->sum('amount')]);
    }

    private function incomeExpense(Carbon $from, Carbon $to): array
    {
        $totals = AccountVoucherEntry::query()->selectRaw('chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.name, chart_of_accounts.account_type, COALESCE(SUM(account_voucher_entries.debit), 0) AS debit, COALESCE(SUM(account_voucher_entries.credit), 0) AS credit')->join('account_vouchers', 'account_voucher_entries.account_voucher_id', '=', 'account_vouchers.id')->join('chart_of_accounts', 'account_voucher_entries.chart_of_account_id', '=', 'chart_of_accounts.id')->whereBetween('account_vouchers.voucher_date', [$from, $to])->whereIn('chart_of_accounts.account_type', ['income', 'expense'])->groupBy('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.account_type')->orderBy('chart_of_accounts.code')->get();
        $income = $totals->where('account_type', 'income')->map(fn ($row) => (object) [...(array) $row, 'amount' => (float) $row->credit - (float) $row->debit]);
        $expenses = $totals->where('account_type', 'expense')->map(fn ($row) => (object) [...(array) $row, 'amount' => (float) $row->debit - (float) $row->credit]);

        return compact('income', 'expenses') + ['totalIncome' => $income->sum('amount'), 'totalExpenses' => $expenses->sum('amount')];
    }

    private function accountBalances(Carbon $asOf)
    {
        $opening = AccountOpeningBalance::query()->whereDate('opening_date', '<=', $asOf)->selectRaw('chart_of_account_id, SUM(debit) AS debit, SUM(credit) AS credit')->groupBy('chart_of_account_id')->get()->keyBy('chart_of_account_id');
        $movement = AccountVoucherEntry::query()->selectRaw('chart_of_account_id, SUM(debit) AS debit, SUM(credit) AS credit')->join('account_vouchers', 'account_voucher_entries.account_voucher_id', '=', 'account_vouchers.id')->whereDate('account_vouchers.voucher_date', '<=', $asOf)->groupBy('chart_of_account_id')->get()->keyBy('chart_of_account_id');

        return ChartOfAccount::query()->where('is_transactional', true)->whereIn('account_type', ['asset', 'liability', 'equity'])->orderBy('code')->get()->map(function (ChartOfAccount $account) use ($opening, $movement) {
            $net = (float) ($opening[$account->id]->debit ?? 0) - (float) ($opening[$account->id]->credit ?? 0) + (float) ($movement[$account->id]->debit ?? 0) - (float) ($movement[$account->id]->credit ?? 0);
            return (object) ['code' => $account->code, 'name' => $account->name, 'account_type' => $account->account_type, 'net' => $net];
        })->filter(fn ($row) => abs($row->net) > 0.001)->values();
    }

    private function dates(Request $request): array
    {
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        return [isset($data['from']) ? Carbon::parse($data['from']) : today()->startOfMonth(), isset($data['to']) ? Carbon::parse($data['to']) : today()];
    }
}
