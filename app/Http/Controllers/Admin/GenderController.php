<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gender;
use Illuminate\Http\Request;

class GenderController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view gender')->only('index');
        $this->middleware('permission:create gender')->only(['create', 'store']);
        $this->middleware('permission:edit gender')->only(['edit', 'update']);
        $this->middleware('permission:delete gender')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Gender::latest()->get();
        return view('admin.student.gender.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.student.gender.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        Gender::create($data);
        return redirect()->route('admin.genders.index')->with('success', 'Data has been save successfully.');
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
        $data = Gender::findOrFail($id);
        return view('admin.student.gender.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Gender::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.genders.index')->with('success', 'Data has been Update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Gender::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been Delete successfully.');
    }
}
