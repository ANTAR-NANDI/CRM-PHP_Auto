<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountVoucherController extends Controller
{
    public function index(Request $request, string $type): View
    {
        $this->ensureType($type);
        $vouchers = AccountVoucher::query()->with('creator')->where('voucher_type', $type)->latest('voucher_date')->latest('id')->paginate(20);

        return view('admin.vouchers.index', compact('vouchers', 'type'));
    }

    public function create(string $type): View
    {
        $this->ensureType($type);
        $accounts = ChartOfAccount::query()->where('is_active', true)->where('is_transactional', true)->orderBy('code')->get();
        $cashAccounts = $accounts->where('account_type', 'asset')->values();

        return view('admin.vouchers.create', compact('type', 'accounts', 'cashAccounts'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $this->ensureType($type);
        if ($type === 'journal') {
            return $this->storeJournal($request);
        }

        $data = $request->validate([
            'voucher_date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'account_id' => ['required', 'integer', 'different:cash_account_id', 'exists:chart_of_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'narration' => ['nullable', 'string', 'max:1000'],
        ]);

        $accounts = ChartOfAccount::query()->whereIn('id', [$data['cash_account_id'], $data['account_id']])->where('is_active', true)->where('is_transactional', true)->get()->keyBy('id');
        abort_unless($accounts->count() === 2, 422, 'Both selected accounts must be active transaction accounts.');
        if ($type === 'contra') {
            abort_unless($accounts->every(fn (ChartOfAccount $account) => $account->account_type === 'asset'), 422, 'Contra vouchers can only transfer between asset accounts.');
        }

        $voucher = DB::transaction(function () use ($data, $type, $request): AccountVoucher {
            $voucher = AccountVoucher::create([
                'voucher_number' => $this->nextNumber($type), 'voucher_type' => $type,
                'voucher_date' => $data['voucher_date'], 'narration' => blank($data['narration'] ?? null) ? null : $data['narration'],
                'amount' => $data['amount'], 'user_id' => $request->user()->id,
            ]);
            $debitAccount = $type === 'debit' ? $data['account_id'] : $data['cash_account_id'];
            $creditAccount = $type === 'debit' ? $data['cash_account_id'] : $data['account_id'];
            $voucher->entries()->createMany([
                ['chart_of_account_id' => $debitAccount, 'debit' => $data['amount'], 'credit' => 0],
                ['chart_of_account_id' => $creditAccount, 'debit' => 0, 'credit' => $data['amount']],
            ]);

            return $voucher;
        });

        return redirect()->route('vouchers.show', $voucher)->with('success', ucfirst($type).' voucher saved successfully.');
    }

    public function show(AccountVoucher $voucher): View
    {
        $voucher->load(['entries.account', 'creator']);

        return view('admin.vouchers.show', compact('voucher'));
    }

    private function ensureType(string $type): void
    {
        abort_unless(in_array($type, ['debit', 'credit', 'journal', 'contra'], true), 404);
    }

    private function nextNumber(string $type): string
    {
        $prefix = match ($type) {
            'debit' => 'DV', 'credit' => 'CV', 'journal' => 'JV', 'contra' => 'CTV',
        };
        $number = AccountVoucher::query()->where('voucher_type', $type)->lockForUpdate()->count() + 1;

        return sprintf('%s-%s-%05d', $prefix, now()->format('Ym'), $number);
    }

    private function storeJournal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_date' => ['required', 'date'],
            'narration' => ['nullable', 'string', 'max:1000'],
            'entries' => ['required', 'array', 'min:2'],
            'entries.*.account_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'entries.*.debit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.credit' => ['nullable', 'numeric', 'min:0'],
            'entries.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $entries = collect($data['entries'])->map(fn (array $entry) => [
            ...$entry, 'debit' => (float) ($entry['debit'] ?? 0), 'credit' => (float) ($entry['credit'] ?? 0),
        ])->filter(fn (array $entry) => $entry['account_id'] && ($entry['debit'] > 0 || $entry['credit'] > 0))->values();
        abort_unless($entries->count() >= 2, 422, 'Add at least two journal entry lines.');
        abort_if($entries->contains(fn (array $entry) => $entry['debit'] > 0 && $entry['credit'] > 0), 422, 'A journal line cannot have both a debit and a credit.');
        $debit = $entries->sum('debit');
        $credit = $entries->sum('credit');
        abort_unless(abs($debit - $credit) < 0.001 && $debit > 0, 422, 'Total debit and credit must be equal.');
        $validAccounts = ChartOfAccount::query()->whereIn('id', $entries->pluck('account_id'))->where('is_active', true)->where('is_transactional', true)->count();
        abort_unless($validAccounts === $entries->pluck('account_id')->unique()->count(), 422, 'Every journal account must be active and transactional.');

        $voucher = DB::transaction(function () use ($data, $entries, $debit, $request): AccountVoucher {
            $voucher = AccountVoucher::create(['voucher_number' => $this->nextNumber('journal'), 'voucher_type' => 'journal', 'voucher_date' => $data['voucher_date'], 'narration' => blank($data['narration'] ?? null) ? null : $data['narration'], 'amount' => $debit, 'user_id' => $request->user()->id]);
            $voucher->entries()->createMany($entries->map(fn (array $entry) => ['chart_of_account_id' => $entry['account_id'], 'debit' => $entry['debit'], 'credit' => $entry['credit'], 'note' => blank($entry['note'] ?? null) ? null : $entry['note']])->all());

            return $voucher;
        });

        return redirect()->route('vouchers.show', $voucher)->with('success', 'Journal voucher saved successfully.');
    }
}
