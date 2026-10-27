<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadStatusHistory;
use App\Models\Todo;
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
            ->when(! $request->user()->can('sales.view-all'), fn ($q) => $q->where('leads.owner_id', $request->user()->id))
            ->when($filters['segment'] ?? null, fn ($q, $id) => $q->where('segment_id', $id))->when($filters['product'] ?? null, fn ($q, $id) => $q->where('product_id', $id))->when($filters['status'] ?? null, fn ($q, $id) => $q->where('lead_status_id', $id))->when($filters['pipeline'] ?? null, fn ($q, $id) => $q->where('pipeline_id', $id))->when($filters['owner'] ?? null, fn ($q, $id) => $q->where('owner_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($match) => $match->where('leads.name', 'like', "%{$term}%")->orWhere('leads.phone', 'like', "%{$term}%")))->latest('lead_date')->paginate(20)->withQueryString();
        $lastActivities = Activity::query()->where('subject_type', 'lead')->whereIn('subject_id', $leads->getCollection()->pluck('id'))->latest('from_at')->get()->unique('subject_id')->keyBy('subject_id');
        return view('leads.index', ['leads' => $leads, 'filters' => $filters, 'lookups' => $this->lookups(), 'lastActivities' => $lastActivities]);
    }

    public function create(): View { return view('leads.create', ['lookups' => $this->lookups()]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (! $request->user()->can('sales.view-all')) $data['owner_id'] = $request->user()->id;
        if ($request->hasFile('business_card')) $data['business_card_path'] = $request->file('business_card')->store('lead-business-cards', 'public');
        unset($data['business_card']);
        $data['created_by'] = $request->user()->id;
        $data['store_id'] = $request->user()->store_id;
        $lead = Lead::create($data);
        $this->recordStatus($lead, $data['lead_status_id'], null, $data['lead_date'], $request->user()->id, 'Initial lead status', $data['remarks'] ?? null);
        return redirect()->route('leads.index')->with('success', 'Lead saved successfully.');
    }

    public function edit(Request $request, Lead $lead): View
    {
        $this->ensureCanManage($request, $lead);

        return view('leads.edit', ['lead' => $lead, 'lookups' => $this->lookups()]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureCanManage($request, $lead);
        if ((int) $request->input('lead_status_id') !== (int) $lead->lead_status_id) {
            $request->validate(['status_note' => ['required', 'string', 'max:5000']]);
        }
        $data = $this->validated($request);
        if (! $request->user()->can('sales.view-all')) $data['owner_id'] = $request->user()->id;

        if ($request->hasFile('business_card')) {
            $data['business_card_path'] = $request->file('business_card')->store('lead-business-cards', 'public');
        }
        unset($data['business_card']);
        $previousStatusId = (int) $lead->lead_status_id;
        $statusChanged = $previousStatusId !== (int) $data['lead_status_id'];
        $lead->update($data);
        if ($statusChanged) {
            $this->recordStatus($lead, $data['lead_status_id'], $previousStatusId, $data['status_date'] ?? now()->toDateString(), $request->user()->id, $data['status_note'] ?? null, $data['remarks'] ?? null);
            $statusName = strtolower((string) DB::table('crm_lead_statuses')->whereKey($data['lead_status_id'])->value('name'));
            if (in_array($statusName, ['won', 'lost'], true)) {
                Todo::query()->where('subject_type', 'lead')->where('subject_id', $lead->id)->where('status', 'pending')->update(['status' => 'completed', 'completed_at' => now()]);
            }
        }

        return redirect()->route('leads.index')->with('success', 'Lead stage and details updated successfully.');
    }

    public function history(Request $request, Lead $lead): View
    {
        $this->ensureCanManage($request, $lead);
        $lead->load(['owner', 'product', 'status']);

        return view('leads.history', [
            'lead' => $lead,
            'history' => $lead->statusHistory()->with(['status', 'changedBy'])->get(),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name'=>['required','string','max:255'],'phone'=>['nullable','string','max:50'],'alternate_phone'=>['nullable','string','max:50'],'email'=>['nullable','email','max:255'],'address'=>['nullable','string','max:2000'],'job_title'=>['nullable','string','max:255'],'organization_id'=>['nullable','exists:crm_organizations,id'],'segment_id'=>['nullable','exists:crm_segments,id'],'product_id'=>['nullable','exists:products,id'],'color_id'=>['nullable','exists:crm_colors,id'],'lead_status_id'=>['required','exists:crm_lead_statuses,id'],'pipeline_id'=>['nullable','exists:crm_pipelines,id'],'owner_id'=>['required','exists:users,id'],'activity_type_id'=>['nullable','exists:activity_types,id'],'lead_date'=>['required','date'],'status_date'=>['nullable','date'],'status_note'=>['nullable','string','max:5000'],'source_id'=>['nullable','exists:crm_sources,id'],'source_detail'=>['nullable','string','max:255'],'remarks'=>['nullable','string','max:5000'],'business_card'=>['nullable','image','max:5120']]);
    }

    private function recordStatus(Lead $lead, int $statusId, ?int $previousStatusId, string $date, int $userId, ?string $note, ?string $leadRemarks): void
    {
        LeadStatusHistory::create(['lead_id' => $lead->id, 'lead_status_id' => $statusId, 'previous_lead_status_id' => $previousStatusId, 'changed_by' => $userId, 'status_date' => $date, 'status_time' => now()->format('H:i:s'), 'note' => $note, 'lead_remarks' => $leadRemarks]);
    }

    private function ensureCanManage(Request $request, Lead $lead): void
    {
        abort_unless($request->user()->can('sales.view-all') || $lead->owner_id === $request->user()->id, 403, 'You can update only your own lead.');
    }

    private function lookups(): array
    {
        $users = User::where('is_active', true)->orderBy('name');
        if (! auth()->user()?->can('sales.view-all')) $users->whereKey(auth()->id());

        return ['organizations'=>DB::table('crm_organizations')->where('is_active',true)->orderBy('name')->get(), 'segments'=>DB::table('crm_segments')->where('is_active',true)->orderBy('name')->get(), 'statuses'=>DB::table('crm_lead_statuses')->where('is_active',true)->orderBy('name')->get(), 'pipelines'=>DB::table('crm_pipelines')->where('is_active',true)->orderBy('name')->get(), 'sources'=>DB::table('crm_sources')->where('is_active',true)->orderBy('name')->get(), 'colors'=>DB::table('crm_colors')->where('is_active',true)->orderBy('name')->get(), 'products'=>Product::where('is_active',true)->orderBy('name')->get(['id','name']), 'users'=>$users->get(['id','name']), 'activityTypes'=>ActivityType::where('is_active',true)->orderBy('name')->get(['id','name'])];
    }
}
