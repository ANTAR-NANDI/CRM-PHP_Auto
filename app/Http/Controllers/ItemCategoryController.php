<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemCategoryController extends Controller
{
    public function index(): View { return view('admin.item-categories.index', ['categories' => ItemCategory::with('parent')->orderBy('name')->paginate(20)]); }
    public function create(): View { return view('admin.item-categories.create', ['parents' => ItemCategory::orderBy('name')->get()]); }
    public function store(Request $request): RedirectResponse
    {
        ItemCategory::create($request->validate(['name' => ['required', 'string', 'max:255'], 'parent_id' => ['nullable', 'exists:item_categories,id']]));
        return redirect()->route('item-categories.index')->with('success', 'Item category added successfully.');
    }
}
