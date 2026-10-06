<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerVehicleSale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerVehicleSaleController extends Controller
{
    public function create(): View
    {
        return view('customer-sales.create', [
            'saleNo' => 'CVS-'.now()->format('ymd').'-'.str_pad((string) (CustomerVehicleSale::count() + 1), 4, '0', STR_PAD_LEFT),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
            'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'selling_price']),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_no' => ['required', 'string', 'max:50', 'unique:customer_vehicle_sales,sale_no'], 'sale_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'], 'dealer' => ['nullable', 'string', 'max:255'], 'mode_of_sale' => ['required', 'in:cash,credit,booking'], 'delivery_point' => ['nullable', 'string', 'max:255'], 'tentative_delivery_date' => ['nullable', 'date'], 'sales_person_id' => ['nullable', 'exists:users,id'], 'remarks' => ['nullable', 'string', 'max:5000'],
            'segment_id' => ['nullable', 'exists:crm_segments,id'], 'product_id' => ['nullable', 'exists:products,id'], 'color' => ['nullable', 'string', 'max:100'], 'quantity' => ['required', 'integer', 'min:1'], 'body_type' => ['nullable', 'string', 'max:100'], 'tyre_size' => ['nullable', 'string', 'max:100'], 'seat_capacity' => ['nullable', 'integer', 'min:1'], 'registration_type' => ['required', 'in:individual,company'], 'registration_place' => ['nullable', 'string', 'max:100'], 'vehicle_tracker' => ['nullable', 'boolean'],
            'offered_price' => ['nullable', 'numeric', 'min:0'], 'unit_price' => ['nullable', 'numeric', 'min:0'], 'final_price' => ['nullable', 'numeric', 'min:0'], 'paid_amount' => ['nullable', 'numeric', 'min:0'], 'payment_date' => ['nullable', 'date'], 'payment_mode' => ['required', 'in:cash,bank,cheque,mobile_banking'],
        ]);
        $data['vehicle_tracker'] = $request->boolean('vehicle_tracker');
        CustomerVehicleSale::create($data);
        return redirect()->route('customer-sales.index')->with('success', 'Customer sale saved successfully.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'product' => ['nullable', 'integer'], 'dealer' => ['nullable', 'string', 'max:255'], 'sales_person' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'search' => ['nullable', 'string', 'max:255']]);
        $sales = CustomerVehicleSale::query()
            ->leftJoin('customers as customer', 'customer_vehicle_sales.customer_id', '=', 'customer.id')
            ->leftJoin('products as product', 'customer_vehicle_sales.product_id', '=', 'product.id')
            ->leftJoin('users as sales_person', 'customer_vehicle_sales.sales_person_id', '=', 'sales_person.id')
            ->select('customer_vehicle_sales.*', 'customer.name as customer_name', 'customer.phone as customer_phone', 'product.name as product_name', 'sales_person.name as sales_person_name')
            ->when($filters['segment'] ?? null, fn ($query, $value) => $query->where('segment_id', $value))
            ->when($filters['product'] ?? null, fn ($query, $value) => $query->where('product_id', $value))
            ->when($filters['dealer'] ?? null, fn ($query, $value) => $query->where('dealer', $value))
            ->when($filters['sales_person'] ?? null, fn ($query, $value) => $query->where('sales_person_id', $value))
            ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('sale_date', '>=', $value))
            ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('sale_date', '<=', $value))
            ->when($filters['search'] ?? null, fn ($query, $value) => $query->where(fn ($match) => $match->where('sale_no', 'like', "%{$value}%")->orWhere('customer.name', 'like', "%{$value}%")))
            ->latest('sale_date')->paginate(25)->withQueryString();
        return view('customer-sales.index', ['sales' => $sales, 'filters' => $filters, 'segments' => DB::table('crm_segments')->where('is_active', true)->orderBy('name')->get(), 'products' => Product::orderBy('name')->get(['id', 'name']), 'dealers' => CustomerVehicleSale::whereNotNull('dealer')->distinct()->orderBy('dealer')->pluck('dealer'), 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }
}
