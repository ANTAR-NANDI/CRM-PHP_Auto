<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = in_array($request->query('type'), ['retail', 'wholesale'], true) ? $request->query('type') : null;
        $customers = Customer::query()
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->when($type, fn ($query) => $query->where('customer_type', $type))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'search', 'type'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return redirect()->route('customers.index')->with('success', 'Customer saved successfully.');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request, $customer));

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists() || (float) $customer->due_balance > 0) {
            return back()->with('error', 'This customer has sales or an outstanding balance. Mark the customer inactive instead.');
        }
        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_type' => ['required', Rule::in(['retail', 'wholesale'])],
            'phone' => ['required', 'string', 'max:50', Rule::unique('customers')->ignore($customer)],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['email'] = $data['email'] ?: null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
