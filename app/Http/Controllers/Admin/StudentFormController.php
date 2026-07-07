<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\AdmissionDate;
use App\Models\Country;
use App\Models\FormStudent;
use App\Models\FormSubmission;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\PaymentCountry;
use App\Models\RelationShip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StudentFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view admission_studetns')->only('index');
        $this->middleware('permission:create admission_studetns')->only(['create', 'store']);
        $this->middleware('permission:edit admission_studetns')->only(['edit', 'update']);
        $this->middleware('permission:delete admission_studetns')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $data = FormSubmission::latest()->get();
    //     return view('admin.form-students.index',compact('data'));
    // }


      public function index(Request $request)
    {
        // মূল query
        $query = FormSubmission::query();

        // 🔍 Universal Search (job_applied_for, surname, country,)
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('selected_school', 'like', "%$search%")
                ->orWhere('title', 'like', "%$search%")
                ->orWhere('fname', 'like', "%$search%")
                ->orWhere('lname', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('mobile_number', 'like', "%$search%");

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

        // 🧠 Year Filter (via relationship)
        if ($request->filled('year_id')) {
            $year = $request->input('year_id');
            $query->whereHas('students.year', function ($q) use ($year) {
                $q->where('name', $year);
            });
        }

        // 🔽 Sort by latest submission created_at
        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.form-students.index',compact('data'));
    }
    
    public function downloadPDF($id)
    {
        $submission = FormSubmission::findOrFail($id);

        // নতুন PDF ভিউ (emails/pdf/payment-success.blade.php)
        $pdf = Pdf::loadView('emails.pdf.payment-success', [
            'submission' => $submission
        ])->setOptions(['isRemoteEnabled' => true]);

        return $pdf->download('information-parents-' . $submission->id . '.pdf');
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
       
        $data = FormSubmission::with('students')->findOrFail($id);
        
        // সব student_id collect করলাম
        $studentIds = collect($data->packages)->pluck('student_id')->toArray();
        // Student id -> name mapping
        $students = FormStudent::whereIn('id', $studentIds)->pluck('fname','id');
        return view('admin.form-students.show',compact('data','students'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {
    //     // $data = FormSubmission::with('students')->findOrFail($id);


    //     $data = FormSubmission::with('students.studentCourses')->findOrFail($id);
    //     // dd($data);

    //     // dd($data->students);
    //     // $studentIds = collect($data->packages)->pluck('student_id')->toArray();
    //     // $students = FormStudent::whereIn('id', $studentIds)->pluck('fname','id');
    //     return view('admin.form-students.edit',compact('data'));
    // }


   public function edit(string $id)
    {
        
        $data = FormSubmission::with(['students'])->findOrFail($id);
        $country = Country::where('status',1)->get();
        $paymentcountry = PaymentCountry::where('status',1)->get();
        $nationalities = Nationality::where('status',1)->get();
        $admissiondate = AdmissionDate::where('status',1)->get();
        $relationship = RelationShip::all();
        $genders = Gender::all();
        

        return view('admin.form-students.edit', compact('genders','data','country','paymentcountry','nationalities','admissiondate','relationship'));

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = FormSubmission::findOrFail($id);

        $file1 = $request->hasFile('file1') ? ImageHelper::uploadImage($request->file('file1')) : null;
        $file2 = $request->hasFile('file2') ? ImageHelper::uploadImage($request->file('file2')) : null;
        $file3 = $request->hasFile('file3') ? ImageHelper::uploadImage($request->file('file3')) : null;
        $file4 = $request->hasFile('file4') ? ImageHelper::uploadImage($request->file('file4')) : null;
        $signature = $request->hasFile('signature') ? ImageHelper::uploadImage($request->file('signature')) : null;
        
        if ($request->hasFile('file1') && $data->file1) {
            Storage::disk('public')->delete($data->file1);
        }
        if ($request->hasFile('file2') && $data->file2) {
            Storage::disk('public')->delete($data->file2);
        }
        if ($request->hasFile('file3') && $data->file3) {
            Storage::disk('public')->delete($data->file3);
        }
        if ($request->hasFile('file4') && $data->file4) {
            Storage::disk('public')->delete($data->file4);
        }
        if ($request->hasFile('signature') && $data->signature) {
            Storage::disk('public')->delete($data->signature);
        }

        $input = $request->all();

        if ($file1) {
            $input['file1'] = $file1;
        }
        if ($file2) {
            $input['file2'] = $file2;
        }
        if ($file3) {
            $input['file3'] = $file3;
        }
        if ($file4) {
            $input['file4'] = $file4;
        }
        if ($signature) {
            $input['signature'] = $signature;
        }


        $data->update($input);
        return redirect()->route('admin.form-students.index')->with('success','Update Successfully');
    }

    public function singleStudentUpdate(Request $request,$id)
    {
        $data = FormStudent::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        return redirect()->route('admin.form-students.index')->with('success','Student Update Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = FormSubmission::findOrFail($id);
        $data->delete();
        return back()->with('success', 'Status has been successfully updated.');
    }
}


