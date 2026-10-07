<?php

namespace App\Http\Controllers;

use App\Models\InventoryIssue;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorePosition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryIssueController extends Controller
{
    public function index(): View
    {
        return view('inventory-issues.index', ['issues' => InventoryIssue::query()->leftJoin('users as recipient', 'inventory_issues.issue_to_user_id', '=', 'recipient.id')->leftJoin('stores', 'inventory_issues.store_id', '=', 'stores.id')->select('inventory_issues.*', 'recipient.name as recipient_name', 'stores.name as store_name')->withSum('items as total_quantity', 'quantity')->latest('issue_date')->paginate(20)]);
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->withSum('batches as batch_balance', 'quantity_available')->withSum('openingStocks as opening_balance', 'quantity')->orderBy('part_no')->get()->map(fn ($p) => ['id' => $p->id, 'part_no' => $p->part_no, 'name' => $p->name, 'unit' => $p->unit, 'balance' => max(0, (float) $p->batch_balance + (float) $p->opening_balance)]);
        return view('inventory-issues.create', ['nextNo' => $this->nextNumber(), 'products' => $products, 'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'stores' => Store::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'positions' => StorePosition::where('is_active', true)->orderBy('name')->get(['id', 'store_id', 'name'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['issue_date' => ['required', 'date'], 'issue_to_user_id' => ['nullable', 'exists:users,id'], 'store_id' => ['required', 'exists:stores,id'], 'purpose' => ['required', 'string', 'max:255'], 'remarks' => ['nullable', 'string', 'max:5000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'distinct', 'exists:products,id'], 'items.*.store_position_id' => ['nullable', 'exists:store_positions,id'], 'items.*.balance_quantity' => ['nullable', 'numeric', 'min:0'], 'items.*.quantity' => ['required', 'numeric', 'min:.01'], 'items.*.chassis_no' => ['nullable', 'string', 'max:255']]);
        DB::transaction(function () use ($data, $request): void {
            $issue = InventoryIssue::create(collect($data)->except('items')->all() + ['issue_no' => $this->nextNumber(), 'user_id' => $request->user()->id]);
            $issue->items()->createMany($data['items']);
        });
        return redirect()->route('inventory-issues.index')->with('success', 'Issue saved successfully.');
    }

    private function nextNumber(): string { return 'ISS-'.now()->format('ymd').'-'.str_pad((string) (InventoryIssue::whereDate('created_at', now()->toDateString())->count() + 1), 4, '0', STR_PAD_LEFT); }
}
