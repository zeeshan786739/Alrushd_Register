<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RelationShip;
use Illuminate\Http\Request;

class RelationShipController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view relation_ship')->only('index');
        $this->middleware('permission:create relation_ship')->only(['create', 'store']);
        $this->middleware('permission:edit relation_ship')->only(['edit', 'update']);
        $this->middleware('permission:delete relation_ship')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RelationShip::latest()->get();
        return view('admin.student.relation.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.student.relation.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        RelationShip::create($data);
        return redirect()->route('admin.relation-ships.index')->with('success', 'Data has been save successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = RelationShip::findOrFail($id);
        return view('admin.student.relation.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = RelationShip::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.relation-ships.index')->with('success', 'Data has been Update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = RelationShip::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been Delete successfully.');
    }
}
