<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChartOfAccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = ChartOfAccount::query()->with('parent')->orderBy('code')->get();
        $accountsByParent = $accounts->groupBy('parent_id');
        $accountData = $accounts->mapWithKeys(fn (ChartOfAccount $account) => [$account->id => [
            'id' => $account->id, 'code' => $account->code, 'name' => $account->name,
            'parent' => $account->parent?->name ?? 'Chart of Accounts', 'type' => ucfirst($account->account_type),
            'level' => $account->level, 'transactional' => $account->is_transactional, 'active' => $account->is_active,
        ]]);

        return view('admin.accounts.index', compact('accounts', 'accountsByParent', 'accountData'));
    }

    public function subAccounts(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $accounts = ChartOfAccount::query()->with('parent')
            ->whereNotNull('parent_id')
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->orderBy('code')->paginate(30)->withQueryString();

        return view('admin.accounts.sub-accounts', compact('accounts', 'search'));
    }

    public function create(Request $request): View
    {
        $defaultParent = ChartOfAccount::query()->find($request->integer('parent_id'));

        return view('admin.accounts.create', [
            'parents' => ChartOfAccount::query()->orderBy('code')->get(),
            'defaultParentId' => $defaultParent?->id,
            'defaultAccountType' => $defaultParent?->account_type ?? 'asset',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ChartOfAccount::create($this->validated($request));

        return redirect()->route('accounts.index')->with('success', 'Account head created successfully.');
    }

    public function edit(ChartOfAccount $account): View
    {
        return view('admin.accounts.edit', ['account' => $account, 'parents' => ChartOfAccount::query()->whereKeyNot($account)->orderBy('code')->get()]);
    }

    public function update(Request $request, ChartOfAccount $account): RedirectResponse
    {
        $account->update($this->validated($request, $account));

        return redirect()->route('accounts.index')->with('success', 'Account head updated successfully.');
    }

    public function destroy(ChartOfAccount $account): RedirectResponse
    {
        if ($account->children()->exists()) {
            return back()->with('error', 'This account has sub accounts. Remove or move them first.');
        }

        $account->delete();

        return back()->with('success', 'Account head deleted successfully.');
    }

    private function validated(Request $request, ?ChartOfAccount $account = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('chart_of_accounts')->ignore($account)],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('chart_of_accounts', 'id')->whereNot('id', $account?->id)],
            'account_type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'is_transactional' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $parent = isset($data['parent_id']) ? ChartOfAccount::find($data['parent_id']) : null;
        if ($parent && $parent->account_type !== $data['account_type']) {
            return abort(422, 'A sub account must have the same account type as its parent.');
        }

        $data['level'] = $parent ? $parent->level + 1 : 1;
        $data['is_transactional'] = $request->boolean('is_transactional');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
