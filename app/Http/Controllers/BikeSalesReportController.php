<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class BikeSalesReportController {
    public function index(Request $request): View {
        $filters=$request->validate(['segment'=>['nullable','integer','exists:crm_segments,id'],'from'=>['nullable','date'],'to'=>['nullable','date']]);
        $filters['from'] ??= now()->startOfMonth()->toDateString(); $filters['to'] ??= now()->toDateString();
        $sales=DB::table('customer_vehicle_sales as sale')->leftJoin('customers as customer','sale.customer_id','=','customer.id')->leftJoin('products as product','sale.product_id','=','product.id')->leftJoin('crm_segments as segment','sale.segment_id','=','segment.id')->select('sale.*','customer.name as customer_name','product.name as product_name','segment.name as segment_name')->when($filters['segment']??null,fn($q,$v)=>$q->where('sale.segment_id',$v))->whereBetween('sale.sale_date',[$filters['from'],$filters['to']])->latest('sale.sale_date')->paginate(25)->withQueryString();
        return view('bike-sales-reports.index',['sales'=>$sales,'filters'=>$filters,'segments'=>DB::table('crm_segments')->where('is_active',true)->orderBy('name')->get(['id','name'])]);
    }
}
