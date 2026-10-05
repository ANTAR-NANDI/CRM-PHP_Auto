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
        // The original CRM organization table only contains name/status. Keep
        // this page compatible until the optional contact-fields migration runs.
        $organizations = Organization::query()
            ->select('crm_organizations.*', DB::raw('NULL as phone'), DB::raw('NULL as email'), DB::raw('NULL as location_name'), DB::raw('NULL as contact_person'))
            ->when($search, fn ($query) => $query->where('crm_organizations.name', 'like', "%{$search}%"))
            ->orderBy('crm_organizations.name')->paginate(20)->withQueryString();

        return view('organizations.index', compact('organizations', 'search'));
    }
}
