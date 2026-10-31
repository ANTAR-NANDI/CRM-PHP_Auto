<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\CustomerVehicleSale;
use App\Services\AccountingPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChequeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', 'in:in_hand,deposited,cleared,bounced'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'type' => ['nullable', 'in:receive,pay'], 'search' => ['nullable', 'string', 'max:255']]);
        $query = DB::table('cheques')
            ->leftJoin('customers', 'cheques.customer_id', '=', 'customers.id')
            ->leftJoin('customer_vehicle_sales', 'cheques.customer_vehicle_sale_id', '=', 'customer_vehicle_sales.id')
            ->select('cheques.*', 'customers.name as customer_name', 'customer_vehicle_sales.sale_no')
        ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('cheques.status', $value))
        ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('cheques.cheque_date', '>=', $value))
        ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('cheques.cheque_date', '<=', $value))
        ->when($filters['type'] ?? null, fn ($query, $value) => $query->where('cheques.type', $value))
        ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('cheques.cheque_no', 'like', "%{$value}%")->orWhere('customers.name', 'like', "%{$value}%")->orWhere('cheques.account_name', 'like', "%{$value}%")));

        $sales = $this->salesWithDue();

        return view('cheques.smooth-index', [
            'cheques' => $query->latest('cheques.cheque_date')->paginate(25),
            'filters' => $filters,
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
            // Prepare this before rendering. Complex expressions inside @json can
            // be parsed incorrectly by Blade and break the whole cheque page.
            'salesForChequeForm' => $sales->map(function ($sale): array {
                return [
                    'id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'sale_no' => $sale->sale_no,
                    'due' => max(0, (float) $sale->final_price - (float) $sale->paid_amount - (float) $sale->collected_amount),
                ];
            })->values(),
            'bankAccounts' => $this->bankAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['customer_id'=>['required','exists:customers,id'],'customer_vehicle_sale_id'=>['required','exists:customer_vehicle_sales,id'],'cheque_no'=>['required','string','max:100'],'cheque_bank'=>['required','string','max:255'],'cheque_date'=>['required','date'],'amount'=>['required','numeric','min:.01'],'remarks'=>['nullable','string','max:5000']]);
        $sale = CustomerVehicleSale::query()->whereKey($data['customer_vehicle_sale_id'])->where('customer_id', $data['customer_id'])->firstOrFail();
        $due = $this->saleDue($sale);
        if ((float) $data['amount'] > $due + 0.001) throw ValidationException::withMessages(['amount' => 'Cheque amount cannot exceed the remaining invoice due.']);
        $customer = Customer::findOrFail($data['customer_id']);
        DB::table('cheques')->insert([
            'customer_id' => $customer->id,
            'customer_vehicle_sale_id' => $sale->id,
            'account_name' => $customer->name.' — '.$data['cheque_bank'],
            'cheque_no' => $data['cheque_no'],
            'cheque_date' => $data['cheque_date'],
            'amount' => $data['amount'],
            'status' => 'in_hand',
            'type' => 'receive',
            'remarks' => $data['remarks'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success','Cheque saved as In Hand.');
    }

    public function updateStatus(Request $request, int $cheque): RedirectResponse
    {
        $data = $request->validate(['status' => ['required','in:in_hand,deposited,cleared,bounced'], 'cleared_cash_account_id' => ['nullable','exists:chart_of_accounts,id'], 'clear_date' => ['nullable', 'date']]);
        DB::transaction(function () use ($data, $cheque, $request): void {
            $chequeRow = DB::table('cheques')->where('id', $cheque)->lockForUpdate()->first();
            abort_unless($chequeRow, 404);
            if ($data['status'] === 'cleared') {
                if ($chequeRow->sales_collection_id) {
                    throw ValidationException::withMessages(['status' => 'This cheque is already cleared. Its bank account cannot be changed without a reversal.']);
                }
                $bank = $this->bankAccounts()->firstWhere('id', (int) ($data['cleared_cash_account_id'] ?? 0));
                if (! $bank) throw ValidationException::withMessages(['cleared_cash_account_id' => 'Select the bank account where this cheque cleared.']);
                $sale = CustomerVehicleSale::findOrFail($chequeRow->customer_vehicle_sale_id);
                $due = $this->saleDue($sale);
                if ((float) $chequeRow->amount > $due + 0.001) throw ValidationException::withMessages(['status' => 'Cheque amount exceeds the current invoice due.']);
                $clearDate = $data['clear_date'] ?? today()->toDateString();
                $voucher = app(AccountingPostingService::class)->postCustomerVehicleCollection($sale->load('customer'), $bank, (float) $chequeRow->amount, $clearDate, $request->user(), 'Cheque '.$chequeRow->cheque_no.' cleared for vehicle sale '.$sale->sale_no);
                $collectionId = DB::table('sales_collections')->insertGetId(['customer_vehicle_sale_id' => $sale->id, 'collection_type' => 'sales', 'amount' => $chequeRow->amount, 'received_at' => $clearDate, 'payment_mode' => 'cheque', 'bank_head' => $bank->code.' — '.$bank->name, 'remarks' => 'Cheque '.$chequeRow->cheque_no.' cleared.', 'account_voucher_id' => $voucher->id, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('cheques')->where('id', $chequeRow->id)->update(['status' => 'cleared', 'cleared_cash_account_id' => $bank->id, 'cleared_at' => $clearDate, 'sales_collection_id' => $collectionId, 'updated_at' => now()]);
                return;
            }

            if ($chequeRow->sales_collection_id && $data['status'] !== 'cleared') {
                throw ValidationException::withMessages(['status' => 'A cleared cheque cannot be changed. Create a reversal first.']);
            }
            DB::table('cheques')->where('id', $chequeRow->id)->update(['status' => $data['status'], 'updated_at' => now()]);
        });

        return back()->with('success', 'Cheque status updated successfully.');
    }

    private function bankAccounts()
    {
        $parentId = ChartOfAccount::query()->where('code', '1000102')->value('id');

        return ChartOfAccount::query()->where('is_active', true)->where('is_transactional', true)->where('account_type', 'asset')
            ->when($parentId, fn ($query) => $query->where('parent_id', $parentId), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('code')->get(['id', 'code', 'name']);
    }

    private function salesWithDue()
    {
        $collections = DB::table('sales_collections')->selectRaw('customer_vehicle_sale_id, SUM(amount) as collected_amount')->groupBy('customer_vehicle_sale_id');

        return CustomerVehicleSale::query()->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->leftJoinSub($collections, 'collections', fn ($join) => $join->on('customer_vehicle_sales.id', '=', 'collections.customer_vehicle_sale_id'))
            ->select('customer_vehicle_sales.id', 'customer_vehicle_sales.customer_id', 'customer_vehicle_sales.sale_no', 'customer_vehicle_sales.final_price', 'customer_vehicle_sales.paid_amount', 'customers.name as customer_name', DB::raw('COALESCE(collections.collected_amount, 0) as collected_amount'))
            ->whereNotNull('customer_vehicle_sales.customer_id')->orderByDesc('customer_vehicle_sales.sale_date')->get();
    }

    private function saleDue(CustomerVehicleSale $sale): float
    {
        $collected = (float) DB::table('sales_collections')->where('customer_vehicle_sale_id', $sale->id)->sum('amount');

        return max(0, (float) $sale->final_price - (float) $sale->paid_amount - $collected);
    }
}
