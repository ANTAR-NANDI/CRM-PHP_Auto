<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', 'in:retail,wholesale'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $customers = Customer::query()->where('is_active', true)
            ->when($data['type'] ?? null, fn ($query, $type) => $query->where('customer_type', $type))
            ->when($data['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('name')->limit(50)->get(['id', 'name', 'customer_type', 'phone', 'due_balance']);

        return response()->json(['data' => $customers]);
    }
}
