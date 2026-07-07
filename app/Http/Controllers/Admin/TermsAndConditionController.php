<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TermsAndConditionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view terms_condition')->only('index');
        $this->middleware('permission:create terms_condition')->only(['create', 'store']);
        $this->middleware('permission:edit terms_condition')->only(['edit', 'update']);
        $this->middleware('permission:delete terms_condition')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TermsAndCondition::first();
        return view('admin.student.terms.index',compact('data'));
    }
    public function staffTerms()
    {
        $data = TermsAndCondition::first();
        return view('admin.student.terms.staff',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = TermsAndCondition::findOrFail($id);
        $imageone =$request->hasFile('image_one') ? ImageHelper::uploadImage($request->file('image_one')) : null;
        $imagetwo =$request->hasFile('image_two') ? ImageHelper::uploadImage($request->file('image_two')) : null;

        if($request->hasFile('image_one') && $data->image_one){
            Storage::disk('public')->delete($data->image_one);
        }

         if($request->hasFile('image_two') && $data->image_two){
            Storage::disk('public')->delete($data->image_two);
        }

        $input = $request->all();

        $input['image_one'] =$imageone;
        $input['image_two'] =$imagetwo;

        $data->update($input);

        return redirect()->back()->with('success', 'Data has been Update Succesfully');


       

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

