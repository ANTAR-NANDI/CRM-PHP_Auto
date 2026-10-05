<?php

namespace App\Http\Controllers;

use App\Models\AccountOpeningBalance;
use App\Models\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountOpeningBalanceController extends Controller
{
    public function index(): View
    {
        $accounts = ChartOfAccount::query()->where('is_transactional', true)->where('is_active', true)->with('parent')->orderBy('code')->get();
        $balances = AccountOpeningBalance::query()->get()->keyBy('chart_of_account_id');

        return view('admin.accounts.opening-balances', compact('accounts', 'balances'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opening_date' => ['required', 'date'],
            'accounts' => ['required', 'array'],
            'accounts.*.id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'accounts.*.debit' => ['nullable', 'numeric', 'min:0'],
            'accounts.*.credit' => ['nullable', 'numeric', 'min:0'],
            'accounts.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $request): void {
            foreach ($data['accounts'] as $row) {
                $debit = (float) ($row['debit'] ?? 0);
                $credit = (float) ($row['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    throw ValidationException::withMessages(['accounts' => 'An account can have either an opening debit or an opening credit, not both.']);
                }

                if ($debit === 0.0 && $credit === 0.0) {
                    AccountOpeningBalance::query()->where('chart_of_account_id', $row['id'])->delete();
                    continue;
                }

                AccountOpeningBalance::updateOrCreate(['chart_of_account_id' => $row['id']], [
                    'opening_date' => $data['opening_date'], 'debit' => $debit, 'credit' => $credit,
                    'notes' => blank($row['notes'] ?? null) ? null : $row['notes'], 'user_id' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Opening balances saved successfully.');
    }
}
