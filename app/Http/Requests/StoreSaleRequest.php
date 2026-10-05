<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            // A medicine may be sold as both strips and individual pieces in one invoice.
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.sale_unit' => ['required', Rule::in(['piece', 'strip'])],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'customer_type' => ['required', Rule::in(['walking', 'retail', 'wholesale'])],
            'customer_id' => ['nullable', 'required_unless:customer_type,walking', 'integer', 'exists:customers,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'mobile_banking'])],
        ];
    }
}
