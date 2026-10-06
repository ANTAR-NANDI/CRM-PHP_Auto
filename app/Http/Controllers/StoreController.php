<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        return view('admin.stores.index', ['stores' => Store::query()->withCount(['users', 'positions'])->when($search, fn ($query) => $query->where(fn ($match) => $match->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))->orderBy('name')->paginate(20)->withQueryString(), 'search' => $search]);
    }

    public function create(): View
    {
        return view('admin.stores.create', ['store' => new Store()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Store::create($this->validated($request));

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    }

    public function edit(Store $store): View
    {
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $store->update($this->validated($request, $store));

        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        if ($store->users()->exists() || $store->positions()->exists()) {
            return back()->with('error', 'This store has employees or positions. Mark it inactive instead.');
        }

        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Store deleted successfully.');
    }

    private function validated(Request $request, ?Store $store = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('stores', 'code')->ignore($store)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = $data['code'] ?: $this->codeFor($data['name'], $store);

        return $data;
    }

    private function codeFor(string $name, ?Store $store = null): string
    {
        $base = Str::upper(Str::substr(Str::slug($name), 0, 45)) ?: 'STORE';
        $code = $base;
        $number = 2;
        while (Store::query()->where('code', $code)->when($store, fn ($query) => $query->whereKeyNot($store->id))->exists()) {
            $code = Str::substr($base, 0, 45 - strlen((string) $number)).'-'.$number++;
        }

        return $code;
    }
}
