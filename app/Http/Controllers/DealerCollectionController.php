<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DealerCollectionController extends Controller
{
    public function index(): View
    {
        $collections = DB::table('dealer_collections')
            ->join('dealer_sales', 'dealer_collections.dealer_sale_id', '=', 'dealer_sales.id')
            ->join('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')
            ->join('chart_of_accounts', 'dealer_collections.cash_account_id', '=', 'chart_of_accounts.id')
            ->select('dealer_collections.*', 'dealer_sales.sale_no', 'dealers.name as dealer_name', 'chart_of_accounts.code as account_code', 'chart_of_accounts.name as account_name')
            ->latest('dealer_collections.received_at')->latest('dealer_collections.id')->paginate(20);

        return view('dealer-collections.index', compact('collections'));
    }

    public function create(): View
    {
        return view('dealer-collections.create', [
            'sales' => $this->salesWithBalances(),
            'cashAccounts' => $this->cashAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dealer_sale_id' => ['required', 'exists:dealer_sales,id'],
            'cash_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_at' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank,cheque,mobile_banking'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $cash = $this->cashAccounts()->firstWhere('id', (int) $data['cash_account_id']);
        abort_unless($cash, 422, 'Select an active Cash or Bank account.');

        DB::transaction(function () use ($data, $request): void {
            $sale = DB::table('dealer_sales')->where('id', $data['dealer_sale_id'])->lockForUpdate()->first();
            $total = (float) DB::table('dealer_sale_items')->where('dealer_sale_id', $sale->id)->sum('amount');
            $laterReceived = (float) DB::table('dealer_collections')->where('dealer_sale_id', $sale->id)->sum('amount');
            $due = max(0, $total - (float) $sale->paid_amount - $laterReceived);

            if ((float) $data['amount'] > $due + 0.001) {
                throw ValidationException::withMessages(['amount' => 'Collection amount cannot exceed the dealer outstanding balance.']);
            }

            DB::table('dealer_collections')->insert($data + ['user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        });

        return redirect()->route('dealer-collections.index')->with('success', 'Dealer collection saved successfully.');
    }

    private function cashAccounts()
    {
        $parentIds = ChartOfAccount::query()->whereIn('code', ['1000101', '1000102', '1000103'])->pluck('id');

        return ChartOfAccount::query()->where('is_active', true)->where('is_transactional', true)->where('account_type', 'asset')
            ->where(fn ($query) => $query->whereIn('id', $parentIds)->orWhereIn('parent_id', $parentIds))
            ->orderBy('code')->get(['id', 'code', 'name']);
    }

    private function salesWithBalances()
    {
        $items = DB::table('dealer_sale_items')->selectRaw('dealer_sale_id, SUM(amount) as total_amount')->groupBy('dealer_sale_id');
        $collections = DB::table('dealer_collections')->selectRaw('dealer_sale_id, SUM(amount) as collected_amount')->groupBy('dealer_sale_id');

        return DB::table('dealer_sales')
            ->join('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')
            ->leftJoinSub($items, 'items', fn ($join) => $join->on('dealer_sales.id', '=', 'items.dealer_sale_id'))
            ->leftJoinSub($collections, 'collections', fn ($join) => $join->on('dealer_sales.id', '=', 'collections.dealer_sale_id'))
            ->select('dealer_sales.id', 'dealer_sales.sale_no', 'dealers.name as dealer_name', DB::raw('COALESCE(items.total_amount, 0) as total_amount'), DB::raw('COALESCE(dealer_sales.paid_amount, 0) + COALESCE(collections.collected_amount, 0) as received_amount'))
            ->whereRaw('COALESCE(items.total_amount, 0) > COALESCE(dealer_sales.paid_amount, 0) + COALESCE(collections.collected_amount, 0)')
            ->orderByDesc('dealer_sales.sale_date')->get();
    }
}
