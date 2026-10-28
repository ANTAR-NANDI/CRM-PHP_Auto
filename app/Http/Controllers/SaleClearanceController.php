<?php

namespace App\Http\Controllers;

use App\Models\CustomerVehicleSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleClearanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,cleared'],
        ]);
        $collections = DB::table('sales_collections')
            ->selectRaw('customer_vehicle_sale_id, SUM(amount) as collected_amount')
            ->groupBy('customer_vehicle_sale_id');

        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers', 'customer_vehicle_sales.customer_id', '=', 'customers.id')
            ->leftJoinSub($collections, 'collections', fn ($join) => $join->on('customer_vehicle_sales.id', '=', 'collections.customer_vehicle_sale_id'))
            ->leftJoin('users as cleared_by', 'customer_vehicle_sales.account_cleared_by', '=', 'cleared_by.id')
            ->select('customer_vehicle_sales.*', 'customers.name as customer_name', 'cleared_by.name as cleared_by_name', DB::raw('COALESCE(collections.collected_amount, 0) as collected_amount'))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('customer_vehicle_sales.sale_no', 'like', "%{$value}%")->orWhere('customers.name', 'like', "%{$value}%")))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('customer_vehicle_sales.account_clearance_status', $value))
            ->orderByRaw("customer_vehicle_sales.account_clearance_status = 'cleared'")
            ->latest('customer_vehicle_sales.sale_date')
            ->paginate(25)
            ->withQueryString();

        return view('admin.accounts.sales-clearance', compact('sales', 'filters'));
    }

    public function clear(Request $request, CustomerVehicleSale $customerSale): RedirectResponse
    {
        $data = $request->validate(['account_clearance_note' => ['nullable', 'string', 'max:5000']]);

        DB::transaction(function () use ($customerSale, $data, $request): void {
            $sale = CustomerVehicleSale::query()->lockForUpdate()->findOrFail($customerSale->id);
            if ($sale->account_clearance_status === 'cleared') {
                return;
            }

            $sale->update([
                'account_clearance_status' => 'cleared',
                'account_cleared_by' => $request->user()->id,
                'account_cleared_at' => now(),
                'account_clearance_note' => $data['account_clearance_note'] ?? null,
            ]);
        });

        return back()->with('success', "Sale {$customerSale->sale_no} cleared for delivery challan.");
    }
}
