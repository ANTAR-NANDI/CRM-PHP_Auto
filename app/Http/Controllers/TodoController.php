<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Todo;
use App\Models\TodoType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['type'=>['nullable','integer','exists:todo_types,id'],'status'=>['nullable',Rule::in(['pending','completed'])],'from'=>['nullable','date'],'to'=>['nullable','date']]);
        $filters['status'] = $filters['status'] ?? 'pending';
        $filters['from'] = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $filters['to'] = $filters['to'] ?? now()->toDateString();
        $todos = Todo::query()->with(['type','assignee'])->when($filters['type']??null,fn($q,$id)=>$q->where('todo_type_id',$id))->when($filters['status']??null,fn($q,$status)=>$q->where('status',$status))->when($filters['from']??null,fn($q,$date)=>$q->whereDate('due_at','>=',$date))->when($filters['to']??null,fn($q,$date)=>$q->whereDate('due_at','<=',$date))->orderBy('due_at')->paginate(20)->withQueryString();
        return view('todos.index',['todos'=>$todos,'types'=>TodoType::where('is_active',true)->orderBy('name')->get(),'filters'=>$filters]);
    }
    public function create(): View { return view('todos.create',['types'=>TodoType::where('is_active',true)->orderBy('name')->get(),'users'=>User::where('is_active',true)->orderBy('name')->get(['id','name'])]); }
    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['todo_type_id'=>['required','exists:todo_types,id'],'subject_type'=>['required',Rule::in(['lead','customer'])],'subject_id'=>['nullable','integer'],'task_with'=>['nullable','string','max:255'],'due_at'=>['required','date'],'priority'=>['required',Rule::in(['low','medium','high','urgent'])],'assigned_to'=>['required','exists:users,id'],'remind_before_minutes'=>['required',Rule::in([0,5,10,15,30,60,1440])],'note'=>['nullable','string','max:5000']]);
        $subject=$data['subject_type']==='lead'?Lead::class:Customer::class;
        if (!empty($data['subject_id'])&&!$subject::whereKey($data['subject_id'])->exists()) return back()->withErrors(['subject_id'=>'Choose a valid '.$data['subject_type'].'.'])->withInput();
        Todo::create($data); return redirect()->route('todos.index')->with('success','To-do saved successfully.');
    }
    public function complete(Todo $todo): RedirectResponse { $todo->update(['status'=>'completed','completed_at'=>now()]); return back()->with('success','Task marked complete.'); }
    public function subjects(Request $request): JsonResponse {
        $type=$request->query('type','lead'); $query=trim((string)$request->query('q'));
        $records=$type==='customer'?Customer::query()->where('is_active',true):Lead::query();
        $records=$records->when($query,fn($builder)=>$builder->where(fn($match)=>$match->where('name','like',"%{$query}%")->orWhere('phone','like',"%{$query}%")))->orderBy('name')->limit(50)->get(['id','name','phone']);
        return response()->json($records->map(fn($record)=>['id'=>$record->id,'label'=>trim($record->name.' · '.$record->phone)]));
    }
}
