<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BulkSmsController extends Controller
{
    public function create(): View
    {
        return view('bulk-sms.create', [
            'contacts' => Contact::whereNotNull('mobile_1')->orderBy('name')->get(['id', 'name', 'mobile_1']),
            'leads' => Lead::whereNotNull('phone')->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function index(Request $request): View
    {
        $blasts = DB::table('sms_blasts')->join('users', 'users.id', '=', 'sms_blasts.user_id')
            ->select('sms_blasts.*', 'users.name as user_name')
            ->when($request->filled('status'), fn ($query) => $query->where('sms_blasts.status', $request->string('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('sms_blasts.created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('sms_blasts.created_at', '<=', $request->input('to')))
            ->latest('sms_blasts.created_at')->paginate(20)->withQueryString();

        return view('bulk-sms.index', compact('blasts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:contacts,leads,both,manual'], 'selection_mode' => ['required', 'in:all,selected'],
            'contact_ids' => ['nullable', 'array'], 'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'lead_ids' => ['nullable', 'array'], 'lead_ids.*' => ['integer', 'exists:leads,id'],
            'manual_phones' => ['nullable', 'string', 'max:10000'], 'message' => ['required', 'string', 'max:1000'],
        ]);

        $recipients = collect();
        if ($data['audience'] !== 'manual') {
            if (in_array($data['audience'], ['contacts', 'both'], true)) $recipients = $recipients->merge(Contact::whereNotNull('mobile_1')->when($data['selection_mode'] === 'selected', fn ($query) => $query->whereIn('id', $data['contact_ids'] ?? []))->get(['name', 'mobile_1'])->map(fn ($contact) => ['name' => $contact->name, 'phone' => $contact->mobile_1]));
            if (in_array($data['audience'], ['leads', 'both'], true)) $recipients = $recipients->merge(Lead::whereNotNull('phone')->when($data['selection_mode'] === 'selected', fn ($query) => $query->whereIn('id', $data['lead_ids'] ?? []))->get(['name', 'phone'])->map(fn ($lead) => ['name' => $lead->name, 'phone' => $lead->phone]));
        } else $recipients = collect(preg_split('/[\s,;]+/', trim($data['manual_phones'] ?? '')))->filter()->map(fn ($phone) => ['name' => null, 'phone' => $phone]);

        $recipients = $recipients->filter(fn ($recipient) => filled($recipient['phone']))->unique('phone')->values();
        if ($recipients->isEmpty()) return back()->withErrors(['recipients' => 'Choose at least one recipient.'])->withInput();

        DB::transaction(function () use ($request, $data, $recipients) {
            $blastId = DB::table('sms_blasts')->insertGetId(['user_id' => $request->user()->id, 'audience' => $data['audience'], 'message' => $data['message'], 'recipient_count' => $recipients->count(), 'status' => 'queued', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('sms_blast_recipients')->insert($recipients->map(fn ($recipient) => ['sms_blast_id' => $blastId, 'name' => $recipient['name'], 'phone' => $recipient['phone'], 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()])->all());
        });

        return redirect()->route('bulk-sms.index')->with('success', $recipients->count().' recipients queued for SMS delivery.');
    }
}
