<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(): View
    {
        return view('dealers.index', ['dealers' => Dealer::query()->orderBy('name')->paginate(20)]);
    }

    public function create(): View { return view('dealers.create'); }

    public function store(Request $request): RedirectResponse
    {
        Dealer::create($this->validated($request));
        return redirect()->route('dealers.index')->with('success', 'Dealer added successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:50', 'unique:dealers,code'],
            'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'], 'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
