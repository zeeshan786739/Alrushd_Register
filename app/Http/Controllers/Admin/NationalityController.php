<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view nationality')->only('index');
        $this->middleware('permission:create nationality')->only(['create', 'store']);
        $this->middleware('permission:edit nationality')->only(['edit', 'update']);
        $this->middleware('permission:delete nationality')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $data = Nationality::latest()->get();

        return view('admin.student.nationality.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.student.nationality.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        Nationality::create($data);
        return redirect()->route('admin.nationality.index')->with('success', 'Data has been save successfully.');
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
        $data = Nationality::findOrFail($id);
        return view('admin.student.nationality.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Nationality::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.nationality.index')->with('success', 'Data has been Update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Nationality::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been Delete successfully.');
    }
}
