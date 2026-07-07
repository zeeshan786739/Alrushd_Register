<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionDate;
use Illuminate\Http\Request;

class AdmissiondateController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view admission_date')->only('index');
        $this->middleware('permission:create admission_date')->only(['create', 'store']);
        $this->middleware('permission:edit admission_date')->only(['edit', 'update']);
        $this->middleware('permission:delete admission_date')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = AdmissionDate::latest()->get();
        return view('admin.student.admission-date.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.student.admission-date.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        AdmissionDate::create($data);
        return redirect()->route('admin.admission-date.index')->with('success', 'Data has been save successfully.');
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
        $data = AdmissionDate::findOrFail($id);
        return view('admin.student.admission-date.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = AdmissionDate::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.admission-date.index')->with('success', 'Data has been Update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = AdmissionDate::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been Delete successfully.');
    }
}
