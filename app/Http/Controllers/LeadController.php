<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['segment' => ['nullable', 'integer'], 'product' => ['nullable', 'integer'], 'status' => ['nullable', 'integer'], 'pipeline' => ['nullable', 'integer'], 'owner' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:255']]);
        $leads = Lead::query()->with(['owner', 'product'])
            ->leftJoin('crm_lead_statuses as status', 'leads.lead_status_id', '=', 'status.id')->leftJoin('crm_pipelines as pipeline', 'leads.pipeline_id', '=', 'pipeline.id')->leftJoin('crm_colors as color', 'leads.color_id', '=', 'color.id')
            ->select('leads.*', 'status.name as status_name', 'status.badge_color', 'pipeline.name as pipeline_name', 'pipeline.code as pipeline_code', 'color.name as color_name')
            ->when($filters['segment'] ?? null, fn ($q, $id) => $q->where('segment_id', $id))->when($filters['product'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['status'] ?? null, fn ($q, $id) => $q->where('lead_status_id', $id))->when($filters['pipeline'] ?? null, fn ($q, $id) => $q->where('pipeline_id', $id))->when($filters['owner'] ?? null, fn ($q, $id) => $q->where('owner_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($match) => $match->where('leads.name', 'like', "%{$term}%")->orWhere('leads.phone', 'like', "%{$term}%")))->latest('lead_date')->paginate(20)->withQueryString();
        $lastActivities = Activity::query()->where('subject_type', 'lead')->whereIn('subject_id', $leads->getCollection()->pluck('id'))->latest('from_at')->get()->unique('subject_id')->keyBy('subject_id');
        return view('leads.index', ['leads' => $leads, 'filters' => $filters, 'lookups' => $this->lookups(), 'lastActivities' => $lastActivities]);
    }

    public function create(): View { return view('leads.create', ['lookups' => $this->lookups()]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name'=>['required','string','max:255'],'phone'=>['nullable','string','max:50'],'alternate_phone'=>['nullable','string','max:50'],'email'=>['nullable','email','max:255'],'address'=>['nullable','string','max:2000'],'job_title'=>['nullable','string','max:255'],'organization_id'=>['nullable','exists:crm_organizations,id'],'segment_id'=>['nullable','exists:crm_segments,id'],'product_id'=>['nullable','exists:products,id'],'color_id'=>['nullable','exists:crm_colors,id'],'lead_status_id'=>['required','exists:crm_lead_statuses,id'],'pipeline_id'=>['nullable','exists:crm_pipelines,id'],'owner_id'=>['required','exists:users,id'],'activity_type_id'=>['nullable','exists:activity_types,id'],'lead_date'=>['required','date'],'source_id'=>['nullable','exists:crm_sources,id'],'source_detail'=>['nullable','string','max:255'],'remarks'=>['nullable','string','max:5000'],'business_card'=>['nullable','image','max:5120']]);
        if ($request->hasFile('business_card')) $data['business_card_path'] = $request->file('business_card')->store('lead-business-cards', 'public');
        unset($data['business_card']); Lead::create($data);
        return redirect()->route('leads.index')->with('success', 'Lead saved successfully.');
    }

    private function lookups(): array
    {
        return ['organizations'=>DB::table('crm_organizations')->where('is_active',true)->orderBy('name')->get(), 'segments'=>DB::table('crm_segments')->where('is_active',true)->orderBy('name')->get(), 'statuses'=>DB::table('crm_lead_statuses')->where('is_active',true)->orderBy('name')->get(), 'pipelines'=>DB::table('crm_pipelines')->where('is_active',true)->orderBy('name')->get(), 'sources'=>DB::table('crm_sources')->where('is_active',true)->orderBy('name')->get(), 'colors'=>DB::table('crm_colors')->where('is_active',true)->orderBy('name')->get(), 'products'=>Product::where('is_active',true)->orderBy('name')->get(['id','name']), 'users'=>User::where('is_active',true)->orderBy('name')->get(['id','name']), 'activityTypes'=>ActivityType::where('is_active',true)->orderBy('name')->get(['id','name'])];
    }
}
