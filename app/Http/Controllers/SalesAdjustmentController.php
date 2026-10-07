<?php
namespace App\Http\Controllers;
use App\Models\CustomerVehicleSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class SalesAdjustmentController {
    public function index(Request $request): View {
        $filters=$request->validate(['type'=>['nullable','in:discount,charge,correction'],'from'=>['nullable','date'],'to'=>['nullable','date']]);
        $adjustments=DB::table('sales_adjustments')->leftJoin('customer_vehicle_sales as sale','sales_adjustments.customer_vehicle_sale_id','=','sale.id')->leftJoin('crm_segments as segment','sales_adjustments.segment_id','=','segment.id')->select('sales_adjustments.*','sale.sale_no','segment.name as segment_name')->when($filters['type']??null,fn($q,$v)=>$q->where('adjustment_type',$v))->when($filters['from']??null,fn($q,$v)=>$q->whereDate('adjustment_date','>=',$v))->when($filters['to']??null,fn($q,$v)=>$q->whereDate('adjustment_date','<=',$v))->latest('adjustment_date')->paginate(25)->withQueryString();
        return view('sales-adjustments.index',compact('adjustments','filters'));
    }
    public function create(): View { return view('sales-adjustments.create',['sales'=>CustomerVehicleSale::latest()->get(['id','sale_no','customer_id','segment_id','dealer']),'segments'=>DB::table('crm_segments')->where('is_active',true)->get()]); }
    public function store(Request $request): RedirectResponse {
        $data=$request->validate(['customer_vehicle_sale_id'=>['nullable','exists:customer_vehicle_sales,id'],'segment_id'=>['nullable','exists:crm_segments,id'],'adjustment_type'=>['required','in:discount,charge,correction'],'amount'=>['required','numeric','min:0'],'adjustment_date'=>['required','date'],'remarks'=>['nullable','string']]);
        DB::table('sales_adjustments')->insert($data+['reference_no'=>'SAD-'.now()->format('ymdHis'),'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('sales-adjustments.index')->with('success','Sales adjustment saved.');
    }
}
