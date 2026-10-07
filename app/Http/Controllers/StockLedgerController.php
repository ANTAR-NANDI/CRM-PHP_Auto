<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['category' => ['nullable', 'integer'], 'store' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'item' => ['nullable', 'string', 'max:255']]);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString(); $to = $filters['to'] ?? now()->toDateString();
        $products = Product::query()->with('itemCategory')->where('is_active', true)->when($filters['category'] ?? null, fn($q,$v) => $q->where('item_category_id',$v))->when($filters['item'] ?? null, fn($q,$v) => $q->where(fn($x) => $x->where('name','like',"%{$v}%")->orWhere('part_no','like',"%{$v}%")))->get();
        $opening = DB::table('product_opening_stocks')->select('product_id', DB::raw('SUM(quantity) as qty'))->when($filters['store'] ?? null, fn($q,$v) => $q->where('store_id',$v))->groupBy('product_id')->pluck('qty','product_id');
        $moves = DB::table('stock_movements')->select('product_id', DB::raw("SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as in_qty"), DB::raw("SUM(CASE WHEN quantity_change < 0 THEN -quantity_change ELSE 0 END) as out_qty"))->whereBetween('created_at', [$from.' 00:00:00',$to.' 23:59:59'])->groupBy('product_id')->get()->keyBy('product_id');
        $rows = $products->map(function($p) use($opening,$moves){$o=(float)($opening[$p->id]??0);$m=$moves->get($p->id);$in=(float)($m->in_qty??0);$out=(float)($m->out_qty??0);$rate=(float)$p->unit_price;return (object)['product'=>$p,'opening'=>$o,'in'=>$in,'out'=>$out,'closing'=>$o+$in-$out,'rate'=>$rate];});
        return view('stock-ledger.index', compact('rows','filters','from','to') + ['categories'=>ItemCategory::orderBy('name')->get(),'stores'=>Store::where('is_active',true)->orderBy('name')->get()]);
    }
}
