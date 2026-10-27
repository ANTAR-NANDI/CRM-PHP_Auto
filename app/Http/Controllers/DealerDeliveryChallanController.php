<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerDeliveryChallanController extends Controller
{
    public function index(): View
    {
        $challans = DB::table('dealer_delivery_challans')
            ->join('dealer_sales', 'dealer_delivery_challans.dealer_sale_id', '=', 'dealer_sales.id')
            ->join('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')
            ->select('dealer_delivery_challans.*', 'dealer_sales.sale_no', 'dealers.name as dealer_name')
            ->latest('dealer_delivery_challans.delivered_at')->latest('dealer_delivery_challans.id')->paginate(20);

        return view('dealer-delivery-challans.index', compact('challans'));
    }

    public function create(): View
    {
        $sales = DB::table('dealer_sales')->join('dealers', 'dealer_sales.dealer_id', '=', 'dealers.id')
            ->select('dealer_sales.id', 'dealer_sales.sale_no', 'dealer_sales.delivery_point', 'dealers.name as dealer_name')
            ->latest('dealer_sales.sale_date')->get();

        $nextChallanNo = 'DCH-'.now()->format('ymd').'-'.str_pad((string) (DB::table('dealer_delivery_challans')->count() + 1), 4, '0', STR_PAD_LEFT);

        return view('dealer-delivery-challans.create', compact('sales', 'nextChallanNo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dealer_sale_id' => ['required', 'exists:dealer_sales,id'],
            'challan_no' => ['required', 'string', 'max:50', 'unique:dealer_delivery_challans,challan_no'],
            'delivered_at' => ['required', 'date'],
            'delivery_point' => ['nullable', 'string', 'max:255'],
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'receiver_phone' => ['nullable', 'string', 'max:50'],
            'signed_file' => ['required', 'file', 'max:10240'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $file = $request->file('signed_file');
        DB::table('dealer_delivery_challans')->insert([
            ...collect($data)->except('signed_file')->all(),
            'signed_file_path' => $file->store('dealer-delivery-challans', 'public'),
            'signed_file_name' => $file->getClientOriginalName(),
            'user_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dealer-delivery-challans.index')->with('success', 'Dealer delivery challan saved successfully.');
    }
}
