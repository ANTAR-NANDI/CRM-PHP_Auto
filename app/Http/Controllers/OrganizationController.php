<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $organizations = Organization::query()
            ->leftJoin('crm_locations as location', 'crm_organizations.location_id', '=', 'location.id')
            ->select('crm_organizations.*', 'location.name as location_name')
            ->when($search, fn ($query) => $query->where('crm_organizations.name', 'like', "%{$search}%"))
            ->orderBy('crm_organizations.name')->paginate(20)->withQueryString();

        return view('organizations.index', compact('organizations', 'search'));
    }

    public function create(): View
    {
        return view('organizations.create', ['locations' => DB::table('crm_locations')->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:crm_organizations,name'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'location_id' => ['nullable', 'exists:crm_locations,id'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);
        $data['created_by'] = $request->user()->id;
        $data['store_id'] = $request->user()->store_id;
        $data['is_active'] = true;
        Organization::create($data);

        return redirect()->route('organizations.index')->with('success', 'Organization saved successfully.');
    }
}
