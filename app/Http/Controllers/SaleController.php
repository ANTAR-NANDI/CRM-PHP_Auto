<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function show(Sale $sale): View
    {
        abort_unless(request()->user()->hasAnyPharmacyRole(['admin', 'manager', 'cashier']) || $sale->user_id === request()->user()->id, 403);
        $sale->load(['user', 'customer', 'items.product', 'items.batch']);

        return view('pos.invoice', compact('sale'));
    }
}
