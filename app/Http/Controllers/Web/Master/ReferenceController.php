<?php

namespace App\Http\Controllers\Web\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Reference;

class ReferenceController extends Controller
{
    public function __construct()
    {
        // optional: require auth for create/update/delete
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $main_menu = 'master';
        $sub_menu  = 'references';
        $references = Reference::paginate(10);
    return view('admin.master.references.index', compact('references', 'main_menu', 'sub_menu'));
    }

    public function show(Reference $reference)
    {
        
        $main_menu = 'master';
        $sub_menu  = 'references';
    return view('admin.master.references.show', compact('reference', 'main_menu', 'sub_menu'));
    }

    public function create()
    {
        
        $main_menu = 'master';
        $sub_menu  = 'references';
    return view('admin.master.references.create', compact('main_menu', 'sub_menu'));
    }

    public function fetch(QueryFilterContract $queryFilter)
    {
        $query = $this->model->query();//->active()
        $results = $queryFilter->builder($query)->customFilter(new CommonMasterFilter)->paginate();
        if((config('app.app_for')=="super") || (config('app.app_for')=="bidding"))
        {
        return view('admin.master.carmodel._model', compact('results'));
        }else{
        return view('admin.master.taxi.carmodel._model', compact('results'));
        }
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
        
        $main_menu = 'master';
        $sub_menu  = 'references';
    return view('admin.master.references.edit', compact('reference', 'main_menu', 'sub_menu'));
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
