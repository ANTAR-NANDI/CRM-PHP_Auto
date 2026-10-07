<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Requisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RequisitionController extends Controller
{
    public function index(): View
    {
        return view('requisitions.index', ['requisitions' => Requisition::withSum('items as total_quantity', 'quantity')->latest('requisition_date')->paginate(20)]);
    }

    public function create(): View
    {
        return view('requisitions.create', ['nextNo' => $this->nextNumber(), 'products' => Product::where('is_active', true)->orderBy('part_no')->get(['id', 'part_no', 'name'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'requisition_date' => ['required', 'date'], 'remarks' => ['nullable', 'string', 'max:5000'], 'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'], 'items.*.quantity' => ['required', 'numeric', 'min:.01'],
        ]);
        DB::transaction(function () use ($data, $request): void {
            $requisition = Requisition::create(['requisition_no' => $this->nextNumber(), 'requisition_date' => $data['requisition_date'], 'remarks' => $data['remarks'] ?? null, 'user_id' => $request->user()->id]);
            $requisition->items()->createMany($data['items']);
        });
        return redirect()->route('requisitions.index')->with('success', 'Requisition saved successfully.');
    }

    private function nextNumber(): string
    {
        return 'REQ-'.now()->format('ymd').'-'.str_pad((string) (Requisition::whereDate('created_at', now()->toDateString())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
