<?php
namespace App\Http\Controllers;
use App\Models\GenericName; use Illuminate\Http\RedirectResponse; use Illuminate\Http\Request; use Illuminate\View\View;
class GenericNameController extends Controller {
 public function index(): View { return view('admin.generic-names.index', ['genericNames'=>GenericName::query()->orderBy('name')->paginate(15)]); }
 public function create(): View { return view('admin.generic-names.create'); }
 public function store(Request $request): RedirectResponse { GenericName::create($this->validated($request)); return back()->with('success','Generic name added successfully.'); }
 public function edit(GenericName $genericName): View { return view('admin.generic-names.edit', compact('genericName')); }
 public function update(Request $request, GenericName $genericName): RedirectResponse { $genericName->update($this->validated($request,$genericName)); return back()->with('success','Generic name updated successfully.'); }
 public function destroy(GenericName $genericName): RedirectResponse { $genericName->delete(); return back()->with('success','Generic name deleted successfully.'); }
 private function validated(Request $request, ?GenericName $genericName=null): array { return $request->validate(['name'=>['required','string','max:255','unique:generic_names,name,'.($genericName?->id ?? 'NULL')],'is_active'=>['nullable','boolean']]); }
}
