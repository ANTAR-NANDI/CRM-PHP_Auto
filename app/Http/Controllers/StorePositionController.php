<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StorePosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class StorePositionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        return view('admin.store-positions.index', [
            'positions' => StorePosition::query()->with('store')->withCount('users')
                ->when($search, fn ($query) => $query->where(fn ($match) => $match->where('name', 'like', "%{$search}%")->orWhereHas('store', fn ($store) => $store->where('name', 'like', "%{$search}%"))))
                ->orderBy('store_id')->orderBy('name')->paginate(20)->withQueryString(),
            'stores' => $this->stores(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.store-positions.create', ['position' => new StorePosition(), 'stores' => $this->stores()]);
    }

    public function store(Request $request): RedirectResponse
    {
        StorePosition::create($this->validated($request));

        return redirect()->route('store-positions.index')->with('success', 'Store position created successfully.');
    }

    public function edit(StorePosition $storePosition): View
    {
        return view('admin.store-positions.edit', ['position' => $storePosition, 'stores' => $this->stores()]);
    }

    public function update(Request $request, StorePosition $storePosition): RedirectResponse
    {
        $storePosition->update($this->validated($request, $storePosition));

        return redirect()->route('store-positions.index')->with('success', 'Store position updated successfully.');
    }

    public function destroy(StorePosition $storePosition): RedirectResponse
    {
        if ($storePosition->users()->exists()) {
            return back()->with('error', 'This position is assigned to employees. Mark it inactive instead.');
        }

        $storePosition->delete();

        return redirect()->route('store-positions.index')->with('success', 'Store position deleted successfully.');
    }

    private function stores()
    {
        return Store::query()->orderBy('name')->get();
    }

    private function validated(Request $request, ?StorePosition $position = null): array
    {
        $data = $request->validate([
            'store_id' => ['required', Rule::exists('stores', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = $this->codeFor($data['name'], (int) $data['store_id'], $position);

        return $data;
    }

    private function codeFor(string $name, int $storeId, ?StorePosition $position = null): string
    {
        $base = Str::upper(Str::substr(Str::slug($name), 0, 38)) ?: 'POSITION';
        $code = $base;
        $number = 2;
        while (StorePosition::query()->where('store_id', $storeId)->where('code', $code)->when($position, fn ($query) => $query->whereKeyNot($position->id))->exists()) {
            $code = Str::substr($base, 0, 45 - strlen((string) $number)).'-'.$number++;
        }

        return $code;
    }
}
