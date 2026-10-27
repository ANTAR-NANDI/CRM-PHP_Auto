<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerDeliveryController extends Controller
{
    public function orders(Request $request): View
    {
        $filters = $request->validate(['dealer' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:255']]);
        $orders = $this->salesQuery()
            ->when($filters['dealer'] ?? null, fn ($q, $v) => $q->where('dealer_sales.dealer_id', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($x) => $x->where('dealer_sales.sale_no', 'like', "%{$v}%")->orWhere('dealers.name', 'like', "%{$v}%")))
            ->latest('dealer_sales.sale_date')->paginate(20)->withQueryString();
        return view('dealer-delivery.orders', compact('orders', 'filters') + ['dealers' => Dealer::where('is_active', true)->orderBy('name')->get()]);
    }

    public function outstanding(Request $request): View
    {
        $filters = $request->validate(['dealer' => ['nullable', 'integer']]);
        $rows = $this->salesQuery()
            ->when($filters['dealer'] ?? null, fn ($q, $v) => $q->where('dealer_sales.dealer_id', $v))
            ->whereRaw('COALESCE(items.total_amount, 0) > COALESCE(dealer_sales.paid_amount, 0) + COALESCE(collections.collected_amount, 0)')
            ->orderBy('dealers.name')->paginate(20)->withQueryString();
        return view('dealer-delivery.outstanding', compact('rows', 'filters') + ['dealers' => Dealer::where('is_active', true)->orderBy('name')->get()]);
    }

    public function dealerSales(Request $request): View
    {
        $filters = $request->validate(['dealer' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:255']]);
        $sales = $this->salesQuery()->when($filters['dealer'] ?? null, fn ($q, $v) => $q->where('dealer_sales.dealer_id', $v))->when($filters['search'] ?? null, fn ($q, $v) => $q->where('dealer_sales.sale_no', 'like', "%{$v}%"))->latest('dealer_sales.sale_date')->paginate(20)->withQueryString();
        return view('dealer-delivery.sales', compact('sales', 'filters') + ['dealers' => Dealer::where('is_active', true)->orderBy('name')->get()]);
    }

    private function salesQuery()
    {
        $items = DB::table('dealer_sale_items')
            ->selectRaw('dealer_sale_id, SUM(quantity) as total_quantity, SUM(amount) as total_amount')
            ->groupBy('dealer_sale_id');
        $collections = DB::table('dealer_collections')
            ->selectRaw('dealer_sale_id, SUM(amount) as collected_amount')
            ->groupBy('dealer_sale_id');

        return DB::table('dealer_sales')
            ->leftJoin('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')
            ->leftJoinSub($items, 'items', fn ($join) => $join->on('dealer_sales.id', '=', 'items.dealer_sale_id'))
            ->leftJoinSub($collections, 'collections', fn ($join) => $join->on('dealer_sales.id', '=', 'collections.dealer_sale_id'))
            ->select('dealer_sales.id', 'dealer_sales.sale_no', 'dealer_sales.sale_date', 'dealer_sales.tentative_delivery_date', 'dealer_sales.delivery_point', 'dealer_sales.paid_amount', 'dealers.name as dealer_name')
            ->selectRaw('COALESCE(items.total_quantity, 0) as total_quantity')
            ->selectRaw('COALESCE(items.total_amount, 0) as total_amount')
            ->selectRaw('COALESCE(collections.collected_amount, 0) as collected_amount')
            ->selectRaw('COALESCE(dealer_sales.paid_amount, 0) + COALESCE(collections.collected_amount, 0) as received_amount');
    }
}
