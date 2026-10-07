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
            ->havingRaw('COALESCE(SUM(dealer_sale_items.amount), 0) > dealer_sales.paid_amount')
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
        return DB::table('dealer_sales')->leftJoin('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')->leftJoin('dealer_sale_items', 'dealer_sale_items.dealer_sale_id', '=', 'dealer_sales.id')->select('dealer_sales.id', 'dealer_sales.sale_no', 'dealer_sales.sale_date', 'dealer_sales.tentative_delivery_date', 'dealer_sales.delivery_point', 'dealer_sales.paid_amount', 'dealers.name as dealer_name', DB::raw('COALESCE(SUM(dealer_sale_items.quantity), 0) as total_quantity'), DB::raw('COALESCE(SUM(dealer_sale_items.amount), 0) as total_amount'))->groupBy('dealer_sales.id', 'dealer_sales.sale_no', 'dealer_sales.sale_date', 'dealer_sales.tentative_delivery_date', 'dealer_sales.delivery_point', 'dealer_sales.paid_amount', 'dealers.name');
    }
}
