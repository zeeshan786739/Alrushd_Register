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
        $this->middleware('permission:edit terms_condition')->only(['staffTerms', 'update']);
        $this->middleware('permission:delete terms_condition')->only('destroy');
    }

    public function index()
    {
        return view('admin.student.terms.index', [
            'record' => TermsAndCondition::current(),
        ]);
    }

    public function staffTerms()
    {
        return view('admin.student.terms.staff', [
            'data' => TermsAndCondition::current(),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = TermsAndCondition::findOrFail($id);

        $input = [];

        foreach (['terms_description', 'form_description', 'staff_terms_condition'] as $field) {
            if ($request->has($field)) {
                $input[$field] = $request->input($field);
            }
        }

        if ($request->hasFile('image_one')) {
            if ($data->image_one) {
                Storage::disk('public')->delete($data->image_one);
            }
            $input['image_one'] = ImageHelper::uploadImage($request->file('image_one'));
        }

        if ($request->hasFile('image_two')) {
            if ($data->image_two) {
                Storage::disk('public')->delete($data->image_two);
            }
            $input['image_two'] = ImageHelper::uploadImage($request->file('image_two'));
        }

        $data->update($input);

        return redirect()->back()->with('success', 'Data has been Update Succesfully');
    }

    public function destroy(string $id)
    {
        //
    }
}
