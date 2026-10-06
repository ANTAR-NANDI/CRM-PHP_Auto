<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['type' => ['nullable', 'integer'], 'organization' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:255']]);
        $contacts = Contact::query()->leftJoin('crm_contact_types as type', 'contacts.contact_type_id', '=', 'type.id')->leftJoin('crm_organizations as organization', 'contacts.organization_id', '=', 'organization.id')->select('contacts.*', 'type.name as type_name', 'organization.name as organization_name')->when($filters['type'] ?? null, fn ($q, $id) => $q->where('contact_type_id', $id))->when($filters['organization'] ?? null, fn ($q, $id) => $q->where('organization_id', $id))->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($match) => $match->where('contacts.name', 'like', "%{$term}%")->orWhere('contacts.mobile_1', 'like', "%{$term}%")->orWhere('contacts.email', 'like', "%{$term}%")))->latest()->paginate(20)->withQueryString();

        return view('contacts.index', ['contacts' => $contacts, 'filters' => $filters, 'lookups' => $this->lookups()]);
    }

    public function create(): View
    {
        return view('contacts.create', ['lookups' => $this->lookups()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'mobile_1' => ['nullable', 'string', 'max:50'], 'mobile_2' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'organization_id' => ['nullable', 'exists:crm_organizations,id'], 'job_title' => ['nullable', 'string', 'max:255'], 'id_type' => ['nullable', 'string', 'max:50'], 'id_number' => ['nullable', 'string', 'max:100'], 'gender' => ['nullable', 'in:male,female,other'], 'religion' => ['nullable', 'string', 'max:100'], 'birth_date' => ['nullable', 'date'], 'contact_type_id' => ['required', 'exists:crm_contact_types,id'], 'customer_identifier' => ['nullable', 'string', 'max:100'], 'product_id' => ['nullable', 'exists:products,id'], 'remarks' => ['nullable', 'string', 'max:5000'], 'present_address' => ['nullable', 'string', 'max:2000'], 'present_location_id' => ['nullable', 'exists:crm_locations,id'], 'permanent_address' => ['nullable', 'string', 'max:2000'], 'permanent_location_id' => ['nullable', 'exists:crm_locations,id'], 'father_name' => ['nullable', 'string', 'max:255'], 'mother_name' => ['nullable', 'string', 'max:255'], 'marital_status' => ['nullable', 'in:single,married,divorced,widowed'], 'spouse_name' => ['nullable', 'string', 'max:255'], 'marriage_date' => ['nullable', 'date'],
        ]);
        $data['contact_code'] = 'CT-'.now()->format('ymd').'-'.str_pad((string) (Contact::count() + 1), 5, '0', STR_PAD_LEFT);
        $data['created_by'] = $request->user()->id;
        $data['store_id'] = $request->user()->store_id;
        Contact::create($data);

        return redirect()->route('contacts.index')->with('success', 'Contact saved successfully.');
    }

    private function lookups(): array
    {
        return ['types' => DB::table('crm_contact_types')->where('is_active', true)->orderBy('name')->get(), 'locations' => DB::table('crm_locations')->where('is_active', true)->orderBy('name')->get(), 'organizations' => Organization::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name'])];
    }
}
