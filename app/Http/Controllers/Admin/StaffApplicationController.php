<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use App\Models\StaffAdmissionForm;

class StaffApplicationController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:view staff_application_form')->only('index');
        $this->middleware('permission:create staff_application_form')->only(['create', 'store']);
        $this->middleware('permission:edit staff_application_form')->only(['edit', 'update']);
        $this->middleware('permission:delete staff_application_form')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $data = StaffAdmissionForm::where('type','staff')->latest()->get();
    //     $pageTitle = 'Staff Application Lists';
    //     return view('admin.staff-application.index',compact('data','pageTitle'));
    // }

     public function index(Request $request)
    {
        // মূল query
        $query = StaffAdmissionForm::query();

        // 🔍 Universal Search (job_applied_for, surname, country,)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('job_applied_for', 'like', "%$search%")
                ->orWhere('surname', 'like', "%$search%")
                ->orWhere('country', 'like', "%$search%");

            });
        }

        // 📅 Start & End Date Filtering (date_of_submission based)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
                : null;

            $end = $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
                : null;

            $query->where(function ($q) use ($start, $end) {
                if ($start) {
                    $q->where('created_at', '>=', $start);
                }
                if ($end) {
                    $q->where('created_at', '<=', $end);
                }
            });
        }

        // 🔽 Sort by latest submission date
        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        $pageTitle = 'Staff Application Lists';
        return view('admin.staff-application.index',compact('data','pageTitle'));
    }
    
    // public function job()
    // {
    //     $data = JobApplication::latest()->get();
       
    //     return view('admin.job-application.index',compact('data','pageTitle'));
    // }


    public function job(Request $request)
{
    // মূল query
    $query = JobApplication::query();

    // 🔍 Universal Search (name, email, phone, country_of_residence)
    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhere('phone', 'like', "%$search%")
              ->orWhere('country_of_residence', 'like', "%$search%");
        });
    }

    // 📅 Start & End Date Filtering (date_of_submission based)
    if ($request->filled('start_date') || $request->filled('end_date')) {
        $start = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : null;

        $end = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : null;

        $query->where(function ($q) use ($start, $end) {
            if ($start) {
                $q->where('date_of_submission', '>=', $start);
            }
            if ($end) {
                $q->where('date_of_submission', '<=', $end);
            }
        });
    }

    // 🔽 Sort by latest submission date
    $data = $query->orderBy('date_of_submission', 'desc')->paginate(10);

    $pageTitle = 'Job Application Lists';
    return view('admin.job-application.index',compact('data','pageTitle'));
}


    public function jobView($id)
    {
        $data = JobApplication::findOrFail($id);
        $data['status'] = '1';
        $data->save();
        return view('admin.job-application.view',compact('data'));
    }
    public function jobDelete($id)
    {
        $data = JobApplication::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success','Item Delete Successfully');
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
        $data = StaffAdmissionForm::findOrFail($id);
        $data['status'] = '1';
        $data->save();
        return view('admin.staff-application.view',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = StaffAdmissionForm::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('success', 'Data has been deleted successfully.');
    }
    public function updateStatus(Request $request)
    {
        $staff = StaffAdmissionForm::findOrFail($request->id);
        $staff->status = $request->status;
        $staff->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }

}


