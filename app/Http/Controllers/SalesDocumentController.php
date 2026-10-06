<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SalesDocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:255']])['search'] ?? null;
        $documents = DB::table('sales_documents')->when($search, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))->latest()->get();
        return view('sales-documents.index', compact('documents', 'search'));
    }

    public function create()
    {
        return view('sales-documents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'file' => ['required', 'file', 'max:10240'], 'remarks' => ['nullable', 'string', 'max:5000']]);
        $file = $request->file('file');
        DB::table('sales_documents')->insert(['title' => $data['title'], 'path' => $file->store('sales-documents', 'public'), 'original_name' => $file->getClientOriginalName(), 'remarks' => $data['remarks'] ?? null, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('sales-documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function download(int $id)
    {
        $document = DB::table('sales_documents')->find($id);
        abort_unless($document, 404);
        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    public function destroy(int $id)
    {
        $document = DB::table('sales_documents')->find($id);
        abort_unless($document, 404);
        Storage::disk('public')->delete($document->path);
        DB::table('sales_documents')->where('id', $id)->delete();
        return redirect()->route('sales-documents.index')->with('success', 'Document deleted successfully.');
    }
}
