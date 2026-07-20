<?php

namespace App\Http\Controllers;

use App\Models\AdmissionDate;
use App\Models\Country;
use App\Models\FormStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\FormSubmission;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\PaymentCountry;
use App\Models\RelationShip;
use App\Models\School;
use App\Models\StudentCourse;
use App\Models\StudentGroup;
use App\Models\StudentLanguage;
use App\Models\StudentPackage;
use App\Models\StudentSubject;
use App\Models\StudentYear;
use App\Models\TermsAndCondition;
use App\Services\Crm\LeadSyncService;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Svg\Tag\Rect;

class MultiStepFormController extends Controller
{
    public function studentAdmission()
    {
        return view('student.index');
    }

    public function getYears($group_id)
    {
        $years = StudentYear::where('group_id', $group_id)->get(['id', 'name']);
        return response()->json($years);
    }

    // public function getPackages($group_id, $year_id)
    // {
    //     $packages = StudentPackage::all(); // StudentPackage just has names
    //     return response()->json($packages);
    // }
    
    public function getPackages($year_id)
    {
        // $packages = StudentPackage::all(); // StudentPackage just has names
        $packages = StudentPackage::where('status',1)->get();
        return response()->json($packages);
    }
    

    public function getCourseDetails(Request $request)
    {
        $group_id = $request->group_id;
        $year_id = $request->year_id;
        $package_id = $request->package_id;

        $course = StudentCourse::where('group_id', $group_id)
            ->where('year_id', $year_id)
            ->where('package_id', $package_id)
            ->first();

        if (!$course) return response()->json(['error' => 'No course found'], 404);

        // Helper to decode JSON or comma string into array
        $toArray = function ($value) {
            if (!$value) return [];
            if (is_array($value)) return $value;
            if ($json = json_decode($value, true)) return is_array($json) ? $json : [];
            return explode(',', $value); // fallback for comma-separated string
        };

        $coreSubjects = $toArray($course->core_subject);
        $coreNames = StudentSubject::whereIn('id', $coreSubjects)->pluck('name');

        $islamicSubjects = $toArray($course->islamic_subject);
        $islamicNames = StudentSubject::whereIn('id', $islamicSubjects)->pluck('name');

        $additionalSubjects = $toArray($course->additional_subject);
        $additionalNames = StudentSubject::whereIn('id', $additionalSubjects)->pluck('name');

        $languages = $toArray($course->language);
        $languageNames = StudentLanguage::whereIn('id', $languages)->pluck('name');

        return response()->json([
            'core_subject' => $coreNames,
            'islamic_subject' => $islamicNames,
            'additional_subject' => $additionalNames,
            'language' => $languageNames,
            'hifdh' => $course->hifdh ?? 0,
            'hifdh_text' => $course->hifdh_text ?? '',
        ]);
    }

    // ===================== Show step =====================
    public function showStep($step)
    {
        $submissionId = Session::get('submission_id');

        // যদি session না থাকে, Step 1 এ redirect
        if (!$submissionId) {
            $step = 1;
        }

        $data = [];

        if ($submissionId) {
            // main submission + students সহ load করবো
            $submission = FormSubmission::with([
                'students.year',
                'students.package'
            ])->find($submissionId);

            if ($submission) {
                // প্রতিটি student এর জন্য year_id এবং package_id match করে course ডাটা আনা
                foreach ($submission->students as $student) {
                    $student->course = StudentCourse::with(['year', 'package'])
                        ->where('year_id', $student->year_id)
                        ->where('package_id', $student->package_id)
                        ->first();
                }

                // array এ কনভার্ট করা
                $data = $submission->toArray();
                $data['submitted_packages'] = $submission->packages ?? [];
            }
        }

        $schools = School::where('status', 1)->get();
        $groups  = StudentGroup::where('status', 1)->get();
        $years   = StudentYear::where('status', 1)->get();

        $terms = TermsAndCondition::first();
        $nationality = Nationality::where('status',1)->get();
        $genders = Gender::where('status',1)->get();
        $admission_date = AdmissionDate::where('status',1)->get();
        $relation_ships = RelationShip::where('status',1)->get();
        $countries = PaymentCountry::where('status',1)->get();
        $allcountry = Country::where('status',1)->get();

        // dd($data); // চেক করার জন্য
        return view("student.step{$step}", compact('data', 'schools', 'groups', 'years', 'submissionId','terms','nationality','genders','admission_date','relation_ships','countries','allcountry'));
    }



    // ===================== Handle step POST =====================
    public function postStep(Request $request, $step)
    {
        if ($request->header('X-Submission-Id')) {
            Session::put('submission_id', (int) $request->header('X-Submission-Id'));
        }

        $submissionId = $request->header('X-Submission-Id')
            ? (int) $request->header('X-Submission-Id')
            : Session::get('submission_id');
        $sessionUserId = Session::get('user_id'); // session user id

        // যদি session expire হয় বা নেই → Step 1 শুরু হবে
        if (!$submissionId && $step != 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please select a school first.', 'redirect' => '/student-admission/step/1'], 422);
            }
            return redirect()->route('form.step', 1)->with('info', 'Please Select School.');
        }


        // ================= Step 1 =================
        if ($step == 1) {
            $validated = $request->validate([
                'selected_school' => 'nullable|string|max:255',
            ]);

            $submission = FormSubmission::updateOrCreate(
                ['id' => $submissionId],
                [
                    'user_id' => $sessionUserId,
                    'selected_school' => $validated['selected_school'] ?? null,
                    'status' => 'in_progress',
                ]
            );

            Session::put('submission_id', $submission->id);
            Session::put('user_id', $sessionUserId);
        }

        // ================= Step 2 =================
        elseif ($step == 2) {
            $validated = $request->validate([
                'title' => 'required|string',
                'fname' => 'required|string',
                'lname' => 'required|string',
                'relationship' => 'required|string',
                'email' => 'required',
                'confirm_email' => 'required',
                'mobile_number' => 'required|string',
                'home_telephone' => 'nullable|string',
                'work_number' => 'required|string',
                'address' => 'required|string',
                'apartment' => 'nullable|string',
                'city' => 'required|string',
                'province' => 'required|string',
                'postal_code' => 'required|string',
                'country' => 'required|string',
                'file1' => 'nullable|file',
                'file2' => 'nullable|file',
                'secondary_title' => 'nullable|string',
                'secondary_fname' => 'nullable|string',
                'secondary_lname' => 'nullable|string',
                'secondary_relationship' => 'nullable|string',
                'secondary_email' => 'nullable',
                'secondary_confirm_email' => 'nullable',
                'secondary_mobile_number' => 'nullable|string',
                'secondary_home_telephone' => 'nullable|string',
                'secondary_work_number' => 'nullable|string',
                'secondary_address' => 'nullable|string',
                'secondary_apartment' => 'nullable|string',
                'secondary_city' => 'nullable|string',
                'secondary_province' => 'nullable|string',
                'secondary_postal_code' => 'nullable|string',
                'secondaryGuardian' => 'nullable|string',
                'secondary_country' => 'nullable|string',
                'file3' => 'nullable|file',
                'file4' => 'nullable|file',
            ]);

          
            // Handle file uploads
            foreach (['file1', 'file2', 'file3', 'file4'] as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    // Delete old file if exists
                    $oldFile = FormSubmission::where('id', $submissionId)->value($fileKey);
                    if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }

                    // Store new file
                    $validated[$fileKey] = $request->file($fileKey)->store('forms', 'public');
                }
            }

            FormSubmission::where('id', $submissionId)->update($validated);
        }

        // ================= Step 3: Students =================
       elseif ($step == 3) {
            $validated = $request->validate([
                'fname.*' => 'required|string|max:100',
                'lname.*' => 'required|string|max:100',
                'dob.*' => 'required|date',
                'gender.*' => 'required|string',
                'nationality.*' => 'required|string',
                'start_date.*' => 'required|string',
                'year_id.*' => 'required|integer',
                'package_id.*' => 'required|integer',
                'core_subject.*' => 'nullable|string',
                'islamic_subject.*' => 'nullable|string',
                'additional_subject.*' => 'nullable|string',
                'language.*' => 'nullable|string',
                'hifdh_subject.*' => 'nullable|string',
                'hifdh_option.*' => 'nullable|string',
                'student_file1.*' => 'nullable|file',
                'student_file2.*' => 'nullable|file',
            ]);

            $count = count($validated['fname']);

            // আগের student records fetch
            $previousStudents = FormStudent::where('form_submission_id', $submissionId)
                ->orderBy('id', 'asc')
                ->get();

            for ($i = 0; $i < $count; $i++) {
                $previousStudent = $previousStudents[$i] ?? null;

                // student_file1
                if ($request->hasFile("student_file1.$i")) {
                    if ($previousStudent && $previousStudent->student_file1 && Storage::disk('public')->exists($previousStudent->student_file1)) {
                        Storage::disk('public')->delete($previousStudent->student_file1);
                    }
                    $file1Path = $request->file('student_file1')[$i]->store('students', 'public');
                } else {
                    $file1Path = $previousStudent->student_file1 ?? null;
                }

                // student_file2
                if ($request->hasFile("student_file2.$i")) {
                    if ($previousStudent && $previousStudent->student_file2 && Storage::disk('public')->exists($previousStudent->student_file2)) {
                        Storage::disk('public')->delete($previousStudent->student_file2);
                    }
                    $file2Path = $request->file('student_file2')[$i]->store('students', 'public');
                } else {
                    $file2Path = $previousStudent->student_file2 ?? null;
                }

                $studentData = [
                    'form_submission_id' => $submissionId,
                    'fname' => $validated['fname'][$i],
                    'lname' => $validated['lname'][$i],
                    'dob' => $validated['dob'][$i],
                    'gender' => $validated['gender'][$i],
                    'nationality' => $validated['nationality'][$i],
                    'start_date' => $validated['start_date'][$i],
                    'year_id' => $validated['year_id'][$i],
                    'package_id' => $validated['package_id'][$i],
                    'core_subject' => $validated['core_subject'][$i] ?? null,
                    'islamic_subject' => $validated['islamic_subject'][$i] ?? null,
                    'additional_subject' => $validated['additional_subject'][$i] ?? null,
                    'language' => $validated['language'][$i] ?? null,
                    'hifdh_subject' => $validated['hifdh_subject'][$i] ?? null,
                    'hifdh' => !empty($request->hifdh_option[$i]) ? 1 : 0,
                    'student_file1' => $file1Path,
                    'student_file2' => $file2Path,
                ];

                if ($previousStudent) {
                    $previousStudent->update($studentData);
                } else {
                    FormStudent::create($studentData);
                }
            }
        }


        // ================= Step 4 =================
        elseif ($step == 4) {
            $validated = $request->validate([
                'health_care' => 'nullable|string',
                'previus_school' => 'nullable|string',
                'access_protocol' => 'nullable|string',
                'aythority' => 'nullable|string',
                'name' => 'nullable|string',
                'special_education' => 'nullable|string',
                'medical_condition' => 'nullable|string',
                'direct_placement' => 'nullable|string',
                // 'accpet' => 'nullable',
                'placement_detail' => 'nullable',
                'authority' => 'nullable',
                'assigned' => 'nullable',
                'percentage' => 'nullable',
            ]);



            FormSubmission::where('id', $submissionId)->update($validated);
        }

        // ================= Step 5 =================


        elseif ($step == 5) {
            $validated = $request->validate([
                'packages' => 'required|array',
                'packages.*.student_id' => 'required|integer',
                'packages.*.package' => 'required|string',
                'packages.*.regular_price' => 'required',
                'packages.*.discount_price' => 'nullable',
                'packages.*.discount' => 'nullable',
            ],
        [
            'packages.required' => 'Please select a package.',
            'packages.array' => 'Please select a package.',
            
            'packages.*.student_id.required' => 'Please select a student for each package.',
            'packages.*.student_id.integer' => 'Please select a student for each package.',
            
            'packages.*.package.required' => 'Please provide a package name.',
            'packages.*.package.string' => 'Please provide a package name.',
            
            'packages.*.regular_price.required' => 'Please select a package.',
        ]);


            FormSubmission::where('id', $submissionId)->update([
                'packages' => json_encode($validated['packages'])
            ]);
        }


        // ================= Step 6 =================
        // elseif ($step == 6) {
        //     $validated = $request->validate([
        //         'signature' => 'required|string',
        //         'signature_accept' => 'required',
        //     ]);

        //     FormSubmission::where('id', $submissionId)->update([
        //         'signature' => $validated['signature'],
        //         'signature_accept' => $validated['signature_accept'],
        //     ]);
        // }

       elseif($step == 6)
        {

            $validated = $request->validate([
                'signature' => 'required|string',
                'signature_accept' => 'required|',
                'accpet' => 'required|',
            ]);
                    // Save the signature as base64 or file
            $signatureData = $validated['signature'];

            // Convert the base64 string to an image if necessary
            $imageName = 'signature_' . time() . '.png';
            $imagePath = 'signatures/' . $imageName;
            Storage::disk('public')->put($imagePath, base64_decode(str_replace('data:image/png;base64,', '', $signatureData)));

            // Update or store the signature in the database
            FormSubmission::where('id', $submissionId)->update([
                'signature' => $imagePath, // Save the image path
                'signature_accept' => $validated['signature_accept'],
                'accpet' => $validated['accpet'],
            ]);


        }


        // ================= Step 7: Payment =================
        elseif ($step == 7) {

            $validated = $request->validate([
                'payment_email' => 'required|email',
                'payment_country' => 'required|string',
                'payment_postal_code' => 'required|string',
                'payment_accept' => 'accepted',
                'total_amount' => 'required|numeric',
                'payment_method' => 'required|in:stripe,offline',
                'card_holder_name' => 'required_if:payment_method,stripe|nullable|string',
                'stripeToken' => 'required_if:payment_method,stripe|nullable|string',
            ]);

            $submission = FormSubmission::findOrFail($submissionId);

            if ($validated['payment_method'] === 'offline') {
                $submission->update([
                    'payment_email' => $validated['payment_email'],
                    'payment_country' => $validated['payment_country'],
                    'payment_postal_code' => $validated['payment_postal_code'],
                    'payment_accept' => true,
                    'status' => 'pending_payment',
                    'total_amount' => $validated['total_amount'],
                    'payment_date' => now(),
                ]);

                $this->syncAdmissionLead($submission->fresh());

                Session::forget('submission_id');

                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'redirect' => '/payment-success']);
                }

                return redirect()->route('payment.success')
                    ->with('success', 'Offline payment request submitted. Our team will contact you.');
            }

            $totalAmount = $validated['total_amount'] * 100;

            $settings = \App\Models\Setting::first();
            if ($settings && $settings->stripe_secret) {
                \Stripe\Stripe::setApiKey($settings->stripe_secret);
            } else {
                return back()->withErrors(['error' => 'Stripe keys are not configured properly.']);
            }

            try {
                $charge = \Stripe\Charge::create([
                    'amount' => $totalAmount,
                    'currency' => 'gbp',
                    'source' => $validated['stripeToken'],
                    'description' => "Payment for Submission ID: {$submission->id}",
                    'receipt_email' => $validated['payment_email'],
                ]);

                $submission->update([
                    'payment_email' => $validated['payment_email'],
                    'payment_country' => $validated['payment_country'],
                    'payment_postal_code' => $validated['payment_postal_code'],
                    'payment_accept' => true,
                    'card_holder_name' => $validated['card_holder_name'],
                    'status' => 'paid',
                    'paid_amount' => $validated['total_amount'],
                    'total_amount' => $validated['total_amount'],
                    'transaction_id' => $charge->id,
                    'currency' => $charge->currency,
                    'payment_date' => now(),
                ]);

                $this->syncAdmissionLead($submission->fresh());

                \Mail::to($submission->email)->send(new \App\Mail\PaymentSuccessMail($submission));

                Session::forget('submission_id');
                Session::forget('stripe_client_secret');

                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'redirect' => '/payment-success']);
                }

                return redirect()->route('payment.success')
                    ->with('success', 'Payment successful & form submitted!');
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }
                return back()->withErrors(['payment_error' => $e->getMessage()]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'submission_id' => Session::get('submission_id'),
                'next_step' => (int) $step + 1,
            ]);
        }

        return redirect()->route('form.step', $step + 1);
    }


    // Parents Update
    public function parentUpdate($id)
    {
        $data = FormSubmission::findOrFail($id)->toArray();
        $relation_ships = RelationShip::where('status',1)->get();
        $allcountry = Country::where('status',1)->get();
        return view('student.parent-update-form', compact('data','relation_ships','allcountry'));
    }

    public function parentUpdateData(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'relationship' => 'required|string',
            'email' => 'required',
            'confirm_email' => 'required',
            'mobile_number' => 'required|string',
            'home_telephone' => 'nullable|string',
            'work_number' => 'required|string',
            'address' => 'required|string',
            'apartment' => 'nullable|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'file1' => 'nullable|file',
            'file2' => 'nullable|file',
            'secondary_title' => 'nullable|string',
            'secondary_fname' => 'nullable|string',
            'secondary_lname' => 'nullable|string',
            'secondary_relationship' => 'nullable|string',
            'secondary_email' => 'nullable',
            'secondary_confirm_email' => 'nullable',
            'secondary_mobile_number' => 'nullable|string',
            'secondary_home_telephone' => 'nullable|string',
            'secondary_work_number' => 'nullable|string',
            'secondary_address' => 'nullable|string',
            'secondary_apartment' => 'nullable|string',
            'secondary_city' => 'nullable|string',
            'secondary_province' => 'nullable|string',
            'secondary_postal_code' => 'nullable|string',
            'secondary_country' => 'nullable|string',
            'file3' => 'nullable|file',
            'file4' => 'nullable|file',
        ]);

        // Handle file uploads & delete old files if exist
        foreach (['file1', 'file2', 'file3', 'file4'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                // Delete old file if exists
                $oldFile = FormSubmission::where('id', $id)->value($fileKey);
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }

                // Store new file
                $validated[$fileKey] = $request->file($fileKey)->store('forms', 'public');
            }
        }

        FormSubmission::where('id', $id)->update($validated);

        return redirect()->route('form.step', 6);
    }


    // Students
    // Parents Update
    public function studentUpdate($id)
    {
        $student = FormStudent::findOrFail($id)->toArray();
        $groups = StudentGroup::where('status', 1)->get();
        $years = StudentYear::where('status', 1)->get();

        $nationality = Nationality::where('status',1)->get();
        $genders = Gender::where('status',1)->get();
        $admission_date = AdmissionDate::where('status',1)->get();

        return view('student.student-update-form', compact('student', 'groups','years','nationality','genders','admission_date'));
    }

    public function studentUpdateData(Request $request, $id)
    {
        $validated = $request->validate([
            'fname.*' => 'required|string|max:100',
            'lname.*' => 'required|string|max:100',
            'dob.*' => 'required|date',
            'gender.*' => 'required|string',
            'nationality.*' => 'required|string',
            'start_date.*' => 'required|string',
            // 'group_id.*' => 'required|integer',
            'year_id.*' => 'required|integer',
            'package_id.*' => 'required|integer',
            'core_subject.*' => 'nullable|string',
            'islamic_subject.*' => 'nullable|string',
            'additional_subject.*' => 'nullable|string',
            'language.*' => 'nullable|string',
            'hifdh_subject.*' => 'nullable|string',
            'hifdh_option.*' => 'nullable|string',
            'student_file1.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'student_file2.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = FormStudent::findOrFail($id);

        foreach ($validated['fname'] as $index => $fname) {

            // Handle student_file1
            if (isset($request->file('student_file1')[$index])) {
                // Delete old file if exists
                if ($data->student_file1 && Storage::disk('public')->exists($data->student_file1)) {
                    Storage::disk('public')->delete($data->student_file1);
                }
                $file1Path = $request->file('student_file1')[$index]->store('students', 'public');
            } else {
                $file1Path = $data->student_file1;
            }

            // Handle student_file2
            if (isset($request->file('student_file2')[$index])) {
                if ($data->student_file2 && Storage::disk('public')->exists($data->student_file2)) {
                    Storage::disk('public')->delete($data->student_file2);
                }
                $file2Path = $request->file('student_file2')[$index]->store('students', 'public');
            } else {
                $file2Path = $data->student_file2;
            }

            // Update the student record
            $data->update([
                'fname' => $fname,
                'lname' => $validated['lname'][$index],
                'dob' => $validated['dob'][$index],
                'gender' => $validated['gender'][$index],
                'nationality' => $validated['nationality'][$index],
                'start_date' => $validated['start_date'][$index],
                // 'group_id' => $validated['group_id'][$index],
                'year_id' => $validated['year_id'][$index],
                'package_id' => $validated['package_id'][$index],
                'core_subject' => $validated['core_subject'][$index] ?? null,
                'islamic_subject' => $validated['islamic_subject'][$index] ?? null,
                'additional_subject' => $validated['additional_subject'][$index] ?? null,
                'language' => $validated['language'][$index] ?? null,
                'hifdh_subject' => $validated['hifdh_subject'][$index] ?? null,
                'hifdh' => !empty($validated['hifdh_option'][$index]) ? 1 : 0,
                'student_file1' => $file1Path,
                'student_file2' => $file2Path,
            ]);
        }

        return redirect()->route('form.step', 6);
    }

    private function syncAdmissionLead(FormSubmission $submission): void
    {
        try {
            app(LeadSyncService::class)->syncFromFormSubmission($submission);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
