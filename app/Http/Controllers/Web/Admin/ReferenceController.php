<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Reference;

class ReferenceController extends Controller
{
    public function index()
    {
        $references = Reference::paginate(10);
        return view('references.index', compact('references'));
    }

    public function create()
    {
        return view('references.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
        ]);

        Reference::create($request->only('name', 'short_name'));

        return redirect()->route('references.index')->with('success', 'Reference created successfully.');
    }

    public function edit(Reference $reference)
    {
        return view('references.edit', compact('reference'));
    }

    public function update(Request $request, Reference $reference)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
        ]);

        $reference->update($request->only('name', 'short_name'));

        return redirect()->route('references.index')->with('success', 'Reference updated successfully.');
    }

    public function destroy(Reference $reference)
    {
        $reference->delete();
        return redirect()->route('references.index')->with('success', 'Reference deleted successfully.');
    }
}
