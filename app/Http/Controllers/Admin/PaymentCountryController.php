<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentCountry;
use Illuminate\Http\Request;

class PaymentCountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view country')->only('index');
        $this->middleware('permission:create country')->only(['create', 'store']);
        $this->middleware('permission:edit country')->only(['edit', 'update']);
        $this->middleware('permission:delete country')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PaymentCountry::latest()->get();
        return view('admin.student.countries.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.student.countries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        PaymentCountry::create($data);
        return redirect()->route('admin.countries.index')->with('success', 'Data has been save successfully.');
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
        $data = PaymentCountry::findOrFail($id);
        return view('admin.student.countries.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = PaymentCountry::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.countries.index')->with('success', 'Data has been Update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PaymentCountry::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been Delete successfully.');
    }
}
