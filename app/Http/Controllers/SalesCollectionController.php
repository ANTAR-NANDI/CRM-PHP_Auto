<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use App\Models\ChartOfAccount;
use App\Services\AccountingPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesCollectionController extends Controller
{
    public function create(): View
    {
        $collectionTotals = DB::table('sales_collections')->selectRaw('customer_vehicle_sale_id, SUM(amount) as collected_amount')->groupBy('customer_vehicle_sale_id');
        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->leftJoinSub($collectionTotals, 'collection_totals', fn ($join) => $join->on('customer_vehicle_sales.id', '=', 'collection_totals.customer_vehicle_sale_id'))
            ->select('customer_vehicle_sales.*', 'customers.name as customer_name', DB::raw('COALESCE(collection_totals.collected_amount, 0) as collected_amount'))
            ->latest('sale_date')->get();

        $cashAccountParentIds = ChartOfAccount::query()->whereIn('code', ['1000101', '1000102', '1000103'])->pluck('id');
        $cashAccounts = ChartOfAccount::query()->leftJoin('chart_of_accounts as parent', 'chart_of_accounts.parent_id', '=', 'parent.id')
            ->where('chart_of_accounts.is_active', true)->where(fn ($query) => $query->where('chart_of_accounts.is_transactional', true)->orWhere('chart_of_accounts.code', '1000102'))->where('chart_of_accounts.account_type', 'asset')
            ->where(fn ($query) => $query->whereIn('chart_of_accounts.id', $cashAccountParentIds)->orWhereIn('chart_of_accounts.parent_id', $cashAccountParentIds))
            ->orderBy('chart_of_accounts.code')->get(['chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'parent.code as parent_code']);

        return view('sales-collections.create', [
            'sales' => $sales,
            'selectedSaleId' => request()->integer('sale_id') ?: null,
            'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'cashAccounts' => $cashAccounts,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_vehicle_sale_id' => ['required', 'exists:customer_vehicle_sales,id'],
            'collection_type' => ['required', 'in:sales,advance'],
            'amount' => ['required', 'numeric', 'min:.01'],
            'received_at' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank,mobile_banking'],
            'cash_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $cashAccountParentIds = ChartOfAccount::query()->whereIn('code', ['1000101', '1000102', '1000103'])->pluck('id');
        $cashAccount = ChartOfAccount::query()->whereKey($data['cash_account_id'])->where('is_active', true)->where(fn ($query) => $query->where('is_transactional', true)->orWhere('code', '1000102'))->where('account_type', 'asset')
            ->where(fn ($query) => $query->whereIn('id', $cashAccountParentIds)->orWhereIn('parent_id', $cashAccountParentIds))->firstOrFail();
        $cashAccount = app(AccountingPostingService::class)->resolvePostingCashAccount($cashAccount);

        $sale = CustomerVehicleSale::findOrFail($data['customer_vehicle_sale_id']);
        $collected = DB::table('sales_collections')->where('customer_vehicle_sale_id', $sale->id)->sum('amount');
        $balance = max(0, (float) $sale->final_price - (float) $sale->paid_amount - (float) $collected);
        if ((float) $data['amount'] > $balance && $data['collection_type'] === 'sales') {
            return back()->withInput()->withErrors(['amount' => 'Receive amount cannot exceed the remaining balance.']);
        }

        unset($data['cash_account_id']);
        $data['bank_head'] = $cashAccount->code.' — '.$cashAccount->name;
        DB::transaction(function () use ($data, $cashAccount, $sale, $request): void {
            $collectionId = DB::table('sales_collections')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
            $voucher = app(AccountingPostingService::class)->postCustomerVehicleCollection($sale->load('customer'), $cashAccount, (float) $data['amount'], $data['received_at'], $request->user(), 'Vehicle collection '.$sale->sale_no);
            DB::table('sales_collections')->where('id', $collectionId)->update(['account_voucher_id' => $voucher->id, 'updated_at' => now()]);
        });

        return redirect()->route('sales-collections.index')->with('success', 'Collection saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'search' => ['nullable', 'string', 'max:255']]);
        $collections = DB::table('sales_collections')
            ->leftJoin('customer_vehicle_sales as sale', 'sales_collections.customer_vehicle_sale_id', '=', 'sale.id')
            ->leftJoin('customers as customer', 'sale.customer_id', '=', 'customer.id')
            ->select('sales_collections.*', 'sale.sale_no', 'sale.sale_date', 'sale.dealer', 'customer.name as customer_name', 'customer.phone as customer_phone')
            ->when($filters['segment'] ?? null, fn ($query, $value) => $query->where('sale.segment_id', $value))
            ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('sales_collections.received_at', '>=', $value))
            ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('sales_collections.received_at', '<=', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('sale.sale_no', 'like', "%{$value}%")->orWhere('customer.name', 'like', "%{$value}%")->orWhere('customer.phone', 'like', "%{$value}%")))
            ->latest('sales_collections.received_at')->get();
        return view('sales-collections.index', ['collections' => $collections, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function followUp(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:all,paid,due'],
        ]);
        $collectionTotals = DB::table('sales_collections')
            ->selectRaw('customer_vehicle_sale_id, SUM(amount) as collected_amount, MAX(received_at) as last_received_at')
            ->groupBy('customer_vehicle_sale_id');
        $chequeSummary = DB::table('cheques')
            ->selectRaw("customer_vehicle_sale_id, MIN(CASE WHEN status IN ('in_hand', 'deposited') THEN cheque_date END) as next_cheque_date, GROUP_CONCAT(CONCAT(cheque_no, ' | ', DATE_FORMAT(cheque_date, '%d %b %Y'), ' | ', REPLACE(status, '_', ' '), ' | ', FORMAT(amount, 2)) ORDER BY cheque_date SEPARATOR '~~') as cheque_details")
            ->groupBy('customer_vehicle_sale_id');

        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->leftJoinSub($collectionTotals, 'collection_totals', fn ($join) => $join->on('customer_vehicle_sales.id', '=', 'collection_totals.customer_vehicle_sale_id'))
            ->leftJoinSub($chequeSummary, 'cheque_summary', fn ($join) => $join->on('customer_vehicle_sales.id', '=', 'cheque_summary.customer_vehicle_sale_id'))
            ->select('customer_vehicle_sales.id', 'customer_vehicle_sales.sale_no', 'customer_vehicle_sales.sale_date', 'customer_vehicle_sales.final_price', 'customer_vehicle_sales.paid_amount', 'customers.name as customer_name', 'customers.phone as customer_phone', DB::raw('COALESCE(collection_totals.collected_amount, 0) as collected_amount'), 'collection_totals.last_received_at', 'cheque_summary.next_cheque_date', 'cheque_summary.cheque_details')
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('customer_vehicle_sales.sale_no', 'like', "%{$value}%")->orWhere('customers.name', 'like', "%{$value}%")->orWhere('customers.phone', 'like', "%{$value}%")))
            ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('customer_vehicle_sales.sale_date', '>=', $value))
            ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('customer_vehicle_sales.sale_date', '<=', $value))
            ->when(($filters['status'] ?? 'all') === 'paid', fn ($query) => $query->whereRaw('customer_vehicle_sales.final_price <= customer_vehicle_sales.paid_amount + COALESCE(collection_totals.collected_amount, 0)'))
            ->when(($filters['status'] ?? 'all') === 'due', fn ($query) => $query->whereRaw('customer_vehicle_sales.final_price > customer_vehicle_sales.paid_amount + COALESCE(collection_totals.collected_amount, 0)'))
            ->orderByRaw('cheque_summary.next_cheque_date IS NULL, cheque_summary.next_cheque_date')
            ->orderByDesc('customer_vehicle_sales.sale_date')
            ->get();

        return view('sales-collections.follow-up', compact('sales', 'filters'));
    }

    public function export(int $collection): View
    {
        $collection = DB::table('sales_collections')
            ->leftJoin('customer_vehicle_sales as sale', 'sales_collections.customer_vehicle_sale_id', '=', 'sale.id')
            ->leftJoin('customers as customer', 'sale.customer_id', '=', 'customer.id')
            ->where('sales_collections.id', $collection)
            ->select('sales_collections.*', 'sale.sale_no', 'sale.sale_date', 'sale.final_price', 'sale.dealer', 'customer.name as customer_name', 'customer.phone as customer_phone', 'customer.address as customer_address')
            ->first();

        abort_unless($collection, 404);

        return view('sales-collections.export', compact('collection'));
    }
}
