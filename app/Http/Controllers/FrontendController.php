<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\CourseFee;
use App\Models\Group;
use App\Models\GroupYear;
use App\Models\Order;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Qualification;
use App\Models\Student;
use App\Models\User;
use App\Models\Setting;
use App\Models\TimeTable;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as StripeSession;
use App\Mail\PaymentInvoiceMail;
use App\Models\Country;
use Illuminate\Support\Facades\Mail;
use App\Models\Coupon;
use App\Models\Debit;
use App\Models\DebitStudent;
use App\Models\Enquire;
use App\Models\Guardiant;
use App\Models\JobApplication;
use App\Models\MeetSpeaker;
use App\Models\Metting;
use App\Models\OpenEvent;
use App\Models\OpenEventItem;
use App\Models\OpenEventForm;
use App\Models\Form;
use App\Models\Referral;
use App\Models\StaffAdmissionForm;
use App\Models\TermsAndCondition;
use App\Services\WebsiteCmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FrontendController extends Controller
{
    public function index(WebsiteCmsService $cmsService)
    {
        $preview = request('cms_preview') === '1';
        $cms = $cmsService->getForFrontend($preview);

        return view('frontend.index', [
            'heroButtons' => $this->resolveHeroButtons(),
            'formCards' => $this->resolveLandingForms(),
            'landing' => [
                'stats' => $cms['statistics']['items'] ?? config('frontend.landing.stats'),
                'programs' => $cms['programs']['items'] ?? config('frontend.landing.programs'),
                'testimonials' => $cms['testimonials']['items'] ?? config('frontend.landing.testimonials'),
                'faq' => $cms['faq']['items'] ?? config('frontend.landing.faq'),
                'contact' => [
                    'email' => $cms['contact']['email'] ?? config('frontend.landing.contact.email'),
                    'phone' => $cms['contact']['phone'] ?? config('frontend.landing.contact.phone'),
                    'address' => $cms['contact']['address'] ?? config('frontend.landing.contact.address'),
                    'map_embed' => $cms['contact']['map_embed'] ?? config('frontend.landing.contact.map_embed'),
                ],
            ],
            'cms' => $cms,
            'themeCss' => $cmsService->cssVariables($cms),
        ]);
    }

    public function dynamicForm(string $slug)
    {
        $form = Form::query()
            ->where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('legacy_route', $slug)
                    ->orWhere('legacy_route', '/'.$slug);
            })
            ->firstOrFail();

        if ($form->handler === 'custom') {
            $legacy = ltrim((string) $form->legacy_route, '/');

            return redirect('/'.$legacy);
        }

        return view('frontend.dynamic-form', [
            'slug' => $form->slug,
            'formName' => $form->name,
        ]);
    }

    public function dynamicFormSuccess(string $slug)
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->first();

        return view('frontend.dynamic-form-success', [
            'slug' => $slug,
            'form' => $form,
        ]);
    }

    private function resolveHeroButtons(): array
    {
        try {
            $forms = Form::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (Form $form) => $form->hasPlacement('landing'));

            if ($forms->isNotEmpty()) {
                $buttons = $forms->map(fn (Form $form) => [
                    'label' => \Illuminate\Support\Str::title($form->hero_label ?: $form->name),
                    'href' => $form->routePath(),
                    'variant' => $form->hero_variant ?: 'outline',
                ])->values()->all();

                $buttons[] = [
                    'label' => 'Profile',
                    'href' => '/admin/login',
                    'variant' => 'outline',
                ];

                return $buttons;
            }
        } catch (\Throwable) {
            //
        }

        return config('frontend.hero_buttons');
    }

    private function resolveLandingForms(): array
    {
        $iconMap = [
            'student-admission' => 'fa-user-graduate',
            'staff-application' => 'fa-chalkboard-teacher',
            'job-applications' => 'fa-briefcase',
            'debit-form' => 'fa-credit-card',
            'enquire-now' => 'fa-envelope',
            'referral' => 'fa-user-friends',
            'meeting-form' => 'fa-phone',
        ];

        $descMap = [
            'student-admission' => 'Begin your child\'s journey with our comprehensive admission process.',
            'staff-application' => 'Join our team of dedicated educators and staff members.',
            'job-applications' => 'Apply for teaching and administrative positions at Al Rushd.',
            'debit-form' => 'Set up direct debit for convenient fee payments.',
            'enquire-now' => 'Get in touch with our admissions team for more information.',
            'referral' => 'Refer a family and help grow our learning community.',
        ];

        try {
            $forms = Form::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (Form $form) => $form->hasPlacement('landing'));

            if ($forms->isNotEmpty()) {
        $cards = $forms->map(fn (Form $form) => [
                    'label' => \Illuminate\Support\Str::title($form->displayLabel()),
                    'href' => $form->routePath(),
                    'description' => $form->description ?: ($descMap[$form->slug] ?? 'Complete this form online in just a few minutes.'),
                    'icon' => $iconMap[$form->slug] ?? 'fa-file-alt',
                ])->values()->all();

                $cards[] = [
                    'label' => 'Profile',
                    'href' => '/admin/login',
                    'description' => 'Access your account, update details, and manage your profile.',
                    'icon' => 'fa-user-circle',
                ];

                return $cards;
            }
        } catch (\Throwable) {
            //
        }

        return collect(config('frontend.hero_buttons'))->map(function ($button) use ($iconMap, $descMap) {
            $slug = trim(str_replace(['/forms/', '/'], '', $button['href']), '/');

            return [
                'label' => $button['label'],
                'href' => $button['href'],
                'description' => $descMap[$slug] ?? 'Complete this form online in just a few minutes.',
                'icon' => $iconMap[$slug] ?? 'fa-file-alt',
            ];
        })->all();
    }

    /**
     * Send legacy URLs to the unified Form Center renderer when applicable.
     */
    protected function dynamicFormRedirect(string $path): ?\Illuminate\Http\RedirectResponse
    {
        $form = Form::resolveForLegacyRedirect($path);

        if ($form?->usesDynamicRenderer()) {
            return redirect()->route('dynamic-form', ['slug' => $form->slug]);
        }

        return null;
    }

    protected function legacySuccessRedirect(string $path, string $fallbackView): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $form = Form::resolveForLegacyRedirect($path);

        if ($form?->usesDynamicRenderer()) {
            return redirect()->route('dynamic-form.success', ['slug' => $form->slug]);
        }

        return view($fallbackView);
    }

    // Book a call
    // public function bookCall()
    // {
    //     $bookedSlots = Metting::select('date', 'time')->get();
    //     return view('contact.book-call',compact('bookedSlots'));
    // }

    public function fetchEvents()
    {
        // Fetch the Calendly API token from the .env file
        $calendlyToken = env('CALENDLY_TOKEN');

        // Make a GET request to get the list of users
        $usersResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $calendlyToken,
            'Content-Type' => 'application/json',
        ])->get('https://api.calendly.com/users');

        dd($usersResponse->json());

        if ($usersResponse->successful()) {
            // Parse the user data
            $users = $usersResponse->json()['collection'];

            $allEvents = [];

            // Loop through each user to get their scheduled events
            foreach ($users as $user) {
                $userEventsResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $calendlyToken,
                    'Content-Type' => 'application/json',
                ])->get("https://api.calendly.com/scheduled_events?user={$user['uri']}");


                if ($userEventsResponse->successful()) {
                    // Collect events for the current user
                    $events = $userEventsResponse->json()['collection'];
                    $allEvents[] = [
                        'user' => $user,
                        'events' => $events,
                    ];
                }
            }

            // Return the events data to a view
            return view('calendly.users-events', compact('allEvents'));
        } else {
            // Handle errors if fetching users failed
            return back()->with('error', 'Failed to fetch users from Calendly.');
        }
    }

    public function bookCall()
    {
        return view('contact.book-call-calendly');
    }

    public function enquireNow()
    {
        if ($redirect = $this->dynamicFormRedirect('enquire-now')) {
            return $redirect;
        }

        return view('contact.enquire');
    }

    public function referral()
    {
        if ($redirect = $this->dynamicFormRedirect('referral')) {
            return $redirect;
        }

        return view('contact.referral');
    }




    public function contact()
    {
        // ধরলাম table নাম Meeting
        $bookedSlots = Metting::select('date', 'time')->get();
        return view('frontend.contact', compact('bookedSlots'));
    }
    public function mettingStore(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email',
            'location' => 'required|string',
        ]);

        Metting::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'guest_email' => $request->guest_email,
            'location'    => $request->location,
            'message'     => $request->message,
            'duration'    => '15 Min',
            'date'        => $request->date,
            'time'        => $request->time,
            'timezone'    => $request->timezone,
        ]);

        return back()->with('success', 'Meeting scheduled successfully!');
    }

    // Enquire
    public function enquireStore(Request $request)
    {
        $data = $request->all();
        $data['submission_date'] = now()->format('M j, Y  h:i A');

        // Generate serial entry_id
        $lastEntry = Enquire::orderBy('entry_id', 'desc')->first();
        $data['entry_id'] = $lastEntry ? $lastEntry->entry_id + 1 : 1;

        // dd($data);

        Enquire::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Data Submit Successfully');
    }
    // Referral
    public function referralStore(Request $request)
    {
        $data = $request->all();
        $data['submission_date'] = now()->format('M j, Y  h:i A');

        // Generate serial entry_id
        $lastEntry = Referral::orderBy('entry_id', 'desc')->first();
        $data['entry_id'] = $lastEntry ? $lastEntry->entry_id + 1 : 1;

        // dd($data);

        Referral::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Data Submit Successfully');
    }

    // Open Event
    public function openEvent()
    {
        $data = OpenEvent::with(['items' => function ($q) {
            $q->where('status', 1);
        }])->where('status', 1)->get();

        return view('frontend.open-event', compact('data'));
    }

    public function openEventForm($id)
    {
        $data = MeetSpeaker::where('status', 1)->get();
        $event = OpenEventItem::findOrFail($id);
        // dd($event);
        return view('frontend.open-event-form', compact('data', 'event'));
    }

    public function openEventFormStore(Request $request)
    {

        $data = $request->all();

        $data['time'] = $request->input('time', []); // array of selected options

        $data['submission_date'] = now()->format('M j, Y  h:i A');

        // Generate serial entry_id
        $lastEntry = OpenEventForm::orderBy('entry_id', 'desc')->first();
        $data['entry_id'] = $lastEntry ? $lastEntry->entry_id + 1 : 1;

        // Save data
        OpenEventForm::create($data);

        return redirect()->route('open-event-success')->with('success', 'Form submitted successfully!');
    }

    public function debitForm()
    {
        if ($redirect = $this->dynamicFormRedirect('debit-form')) {
            return $redirect;
        }

        $groups = Group::where('status', 1)->get();
        return view('frontend.debitForm', compact('groups'));
    }

    public function debitFormStore(Request $request)
    {
        $request->validate([
            'surename' => 'required|string',
            'email'    => 'required|email',
        ]);

        // student_name / student_group বাদ দিয়ে শুধু Debit টেবিলে লাগবে এমন data নিব
        $debitData = $request->except(['student_name', 'student_group']);

        // main debit save হবে
        $debit = Debit::create($debitData);

        // Child students গুলো DebitStudent model এ যাবে
        if ($request->has('student_name')) {
            foreach ($request->student_name as $index => $name) {
                if (!empty($name) && !empty($request->student_group[$index])) {
                    DebitStudent::create([
                        'debit_id'      => $debit->id,
                        'student_name'  => $name,
                        'student_group' => $request->student_group[$index],
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
    public function debitSubmissionSuccwss()
    {
        return $this->legacySuccessRedirect('debit-form', 'frontend.debitFormSuccess');
    }






    public function staffAdmissionForm()
    {
        if ($redirect = $this->dynamicFormRedirect('staff-application')) {
            return $redirect;
        }

        $country = Country::all();
        $terms = TermsAndCondition::first();
        return view('frontend.staff-admission-form',compact('country','terms'));
        
    }

    public function jobAdmissionForm()
    {
        if ($redirect = $this->dynamicFormRedirect('job-applications')) {
            return $redirect;
        }

        $country = Country::all();
        return view('frontend.job-admission-form',compact('country'));

    }

    public function jobApplicationsSuccess()
    {
        return $this->legacySuccessRedirect('job-applications', 'frontend.job-application-successfull');
    }

    public function staffApplicationsSuccess()
    {
        return $this->legacySuccessRedirect('staff-application', 'frontend.staff-application-successfull');
    }


    public function staffAdmissionFormStore(Request $request)
    {
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'signature' => 'required',
        ]);


        $data = $request->all();
        

        $data = $request->except(['certificate_title', 'certificate_file']); // prevent conflicts

        // Handle multiple certificates
        $certificates = [];
        if ($request->has('certificate_title')) {
            foreach ($request->certificate_title as $index => $title) {
                $filePath = null;

                if ($request->hasFile('certificate_file') && isset($request->file('certificate_file')[$index])) {
                    $filePath = ImageHelper::uploadImage($request->file('certificate_file')[$index]);
                }

                $certificates[] = [
                    'title' => $title,
                    'file'  => $filePath,
                ];
            }
        }

        $data['certificated'] = json_encode($certificates);

        // File Uploads
        $data['profile_photo'] = $request->hasFile('profile_photo') ? ImageHelper::uploadImage($request->file('profile_photo')) : null;
        $data['prof_of_id'] = $request->hasFile('prof_of_id') ? ImageHelper::uploadImage($request->file('prof_of_id')) : null;
        $data['prof_of_address'] = $request->hasFile('prof_of_address') ? ImageHelper::uploadImage($request->file('prof_of_address')) : null;
        // $data['certificated'] = $request->hasFile('certificated') ? ImageHelper::uploadImage($request->file('certificated')) : null;
        $data['dbs_one'] = $request->hasFile('dbs_one') ? ImageHelper::uploadImage($request->file('dbs_one')) : null;
        $data['cv'] = $request->hasFile('cv') ? ImageHelper::uploadImage($request->file('cv')) : null;

        // ✅ Signature Handling
        $signatureData = $request->input('signature');
        $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
        $signatureData = str_replace(' ', '+', $signatureData);
        $imageName = 'signature_' . time() . '.png';
        $imagePath = 'signatures/' . $imageName;

        Storage::disk('public')->put($imagePath, base64_decode($signatureData));
        $data['signature'] = $imagePath;

        StaffAdmissionForm::create($data);

        return response()->json(['redirect' => route('staff-applications.success')]);
    }



    public function jobAdmissionFormStore(Request $request)
    {
        // Basic validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'upload_certificates.*' => 'nullable|file',
            'upload_cv' => 'nullable|file',
            'upload_dbs_certificate' => 'nullable|file',
        ]);

        $data = $request->except([
            'upload_certificates', 'upload_cv', 'upload_dbs_certificate',
            'qualification', 'subject', 'institution', 'graduation_year',
            'current', 'position_held', 'start_date', 'end_date', 'responsibilities', 'reason_for_leaving',
            'refer_name', 'refer_email', 'refer_phone'
        ]);

        $data['date_of_submission'] = now();

        // Handling multiple certificates
        $certificates = [];
        if ($request->hasFile('upload_certificates')) {
            foreach ($request->file('upload_certificates') as $file) {
                $certificates[] = ImageHelper::uploadImage($file);
            }
        }
        $data['upload_certificates'] = $certificates;

        // Handling single files
        $data['upload_cv'] = $request->hasFile('upload_cv') ? ImageHelper::uploadImage($request->file('upload_cv')) : null;
        $data['upload_dbs_certificate'] = $request->hasFile('upload_dbs_certificate') ? ImageHelper::uploadImage($request->file('upload_dbs_certificate')) : null;

        // Education: Ensure it's an array before using foreach
        $education = [];
        if ($request->filled('qualification') && is_array($request->qualification)) {
            foreach ($request->qualification as $key => $qualification) {
                $education[] = [
                    'qualification' => $qualification,
                    'subject' => $request->subject[$key] ?? null,
                    'institution' => $request->institution[$key] ?? null,
                    'graduation_year' => $request->graduation_year[$key] ?? null,
                ];
            }
        }
        $data['education'] = $education;

        // Employment: Ensure it's an array before using foreach
        $employment = [];
        if ($request->filled('current') && is_array($request->current)) {
            foreach ($request->current as $key => $current) {
                $employment[] = [
                    'current' => $current,
                    'position_held' => $request->position_held[$key] ?? null,
                    'employee_start_date' => $request->start_date[$key] ?? null,
                    'employee_end_date' => $request->end_date[$key] ?? null,
                    'responsibilities' => $request->responsibilities[$key] ?? null,
                    'reason_for_leaving' => $request->reason_for_leaving[$key] ?? null,
                ];
            }
        }
        $data['previous_employment'] = $employment;

        // References: Ensure it's an array before using foreach
        $references = [];
        if ($request->filled('refer_name') && is_array($request->refer_name)) {
            foreach ($request->refer_name as $key => $name) {
                $references[] = [
                    'name' => $name,
                    'email' => $request->refer_email[$key] ?? null,
                    'phone' => $request->refer_phone[$key] ?? null,
                ];
            }
        }
        $data['references'] = $references;

        // Save the job application
        JobApplication::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Application submitted successfully!']);
        }

        return redirect()->route('job-applications.success')->with('success', 'Application submitted successfully!');
    }



    // public function jobAdmissionFormStore(Request $request)
    // {
    //     // Basic validation
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'nullable|email|max:255',
    //         'phone' => 'nullable|string|max:50',
    //         'dob' => 'nullable|date',
    //         'upload_certificates.*' => 'nullable|file',
    //         'upload_cv' => 'nullable|file',
    //         'upload_dbs_certificate' => 'nullable|file',
    //     ]);

    //     $data = $request->except([
    //         'upload_certificates', 'upload_cv', 'upload_dbs_certificate',
    //         'qualification', 'subject', 'institution', 'graduation_year',
    //         'current', 'position_held', 'start_date', 'end_date', 'responsibilities', 'reason_for_leaving',
    //         'refer_name', 'refer_email', 'refer_phone'
    //     ]);

    //     $data['date_of_submission'] = now();

    //     // Multiple certificates
    //     $certificates = [];
    //     if ($request->hasFile('upload_certificates')) {
    //         foreach ($request->file('upload_certificates') as $file) {
    //             $certificates[] = ImageHelper::uploadImage($file);
    //         }
    //     }
    //     $data['upload_certificates'] = $certificates;

    //     // Single files
    //     $data['upload_cv'] = $request->hasFile('upload_cv') ? ImageHelper::uploadImage($request->file('upload_cv')) : null;
    //     $data['upload_dbs_certificate'] = $request->hasFile('upload_dbs_certificate') ? ImageHelper::uploadImage($request->file('upload_dbs_certificate')) : null;

    //     // Education JSON
    //     $education = [];
    //     if ($request->filled('qualification')) {
    //         foreach ($request->qualification as $key => $qualification) {
    //             $education[] = [
    //                 'qualification' => $qualification,
    //                 'subject' => $request->subject[$key] ?? null,
    //                 'institution' => $request->institution[$key] ?? null,
    //                 'graduation_year' => $request->graduation_year[$key] ?? null,
    //             ];
    //         }
    //     }
    //     $data['education'] = $education;

    //     // Employment JSON
    //     $employment = [];
    //     if ($request->filled('current')) {
    //         foreach ($request->current as $key => $current) {
    //             $employment[] = [
    //                 'current' => $current,
    //                 'position_held' => $request->position_held[$key] ?? null,
    //                 'employee_start_date' => $request->start_date[$key] ?? null,
    //                 'employee_end_date' => $request->end_date[$key] ?? null,
    //                 'responsibilities' => $request->responsibilities[$key] ?? null,
    //                 'reason_for_leaving' => $request->reason_for_leaving[$key] ?? null,
    //             ];
    //         }
    //     }
    //     $data['previous_employment'] = $employment;

    //     // References JSON
    //     $references = [];
    //     if ($request->filled('refer_name')) {
    //         foreach ($request->refer_name as $key => $name) {
    //             $references[] = [
    //                 'name' => $name,
    //                 'email' => $request->refer_email[$key] ?? null,
    //                 'phone' => $request->refer_phone[$key] ?? null,
    //             ];
    //         }
    //     }
    //     $data['references'] = $references;

    //     // Save
    //     JobApplication::create($data);

    //     if ($request->ajax()) {
    //         return response()->json(['success' => true, 'message' => 'Application submitted successfully!']);
    //     }

    //     return redirect()->route('job-applications.success')->with('success', 'Application submitted successfully!');
    // }



    

    public function studentApplication()
    {
        $groups = Group::where('status', 1)->orderBy('serial')->get();
        $packages = Package::where('status', 1)->get();
        $plans = Plan::where('status', 1)->get();
        return view('frontend.home', compact('groups', 'packages', 'plans'));
    }

    public function step1()
    {
        $guardianId = session('guardian_id');

        if ($guardianId) {
            $step1_data = session('step1_data');
            $user = Guardiant::find($guardianId);

            return view('frontend.step1', compact('step1_data', 'user'));
        }

        return redirect()->route('student.application');
    }

    public function stepOneStore(Request $request, $id)
    {
        // Fetch Guardian from ID
        $guardian = Guardiant::findOrFail($id);

        // Validate form input
        $request->validate([
            'title' => 'required|string|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:guardiants,email,' . $guardian->id,
            'confirm' => 'required|in:Father,Mother,Guardian',
        ]);

        // Update Guardian data
        $guardian->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'confirm' => $request->confirm,
        ]);

        // Store in session
        session()->put('step1_data', [
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'confirm' => $request->confirm,
            'subscribe_newsletter' => $request->has('subscribe_newsletter'),
        ]);

        return redirect()->route('step2');
    }




    public function step2()
    {
        $guardianId = session('guardian_id');

        if ($guardianId) {
            $step2_data = session('step2_data');
            $user = Guardiant::find($guardianId);

            if (!$user) {
                return redirect()->route('student.application')->with('error', 'Guardian not found.');
            }

            return view('frontend.step2', compact('step2_data', 'user'));
        }

        return redirect()->route('student.application');
    }

    public function stepTwoStore(Request $request, $id)
    {
        $request->validate([
            'country' => 'required|string|max:255',
            'contact_number_code' => 'nullable|string|max:10',
            'contact_number' => 'required',
            'address_one' => 'required|string|max:255',
            'address_two' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
        ]);

        // Use Guardiant instead of User
        $guardian = Guardiant::findOrFail($id);

        $guardian->update([
            'country' => $request->country,
            'contact_number_code' => $request->contact_number_code,
            'contact_number' => $request->contact_number,
            'address_one' => $request->address_one,
            'address_two' => $request->address_two,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ]);

        // Store in session for use in step3
        session()->put('step2_data', [
            'country' => $request->country,
            'contact_number_code' => $request->contact_number_code,
            'contact_number' => $request->contact_number,
            'address_one' => $request->address_one,
            'address_two' => $request->address_two,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ]);

        return redirect()->route('step3'); // Your next step route
    }




    public function step3()
    {
        $guardianId = session('guardian_id');

        if ($guardianId) {
            $step3_data = session('step3_data');
            $user = Guardiant::find($guardianId);

            if (!$user) {
                return redirect()->route('student.application')->with('error', 'Guardian not found.');
            }

            $times = TimeTable::where('status', 1)->get();

            return view('frontend.step3', compact('step3_data', 'user', 'times'));
        }

        return redirect()->route('student.application');
    }

    public function updateTimetableForm(Request $request, $id)
    {
        $request->validate([
            'time_table' => 'required|string|max:255',
        ]);

        // Find the Guardian instead of User
        $guardian = Guardiant::findOrFail($id);

        // Optional: You can check if session guardian_id matches the ID to authorize
        if (session('guardian_id') != $guardian->id) {
            abort(403, 'Unauthorized action.');
        }

        $guardian->time_table = $request->time_table;
        $guardian->save();

        return redirect()->route('step4')->with('success', 'Timetable updated successfully.');
    }


    public function step4()
    {
        $guardianId = session('guardian_id');

        if ($guardianId) {
            $user = Guardiant::find($guardianId);

            if (!$user) {
                return redirect()->route('student.application')->with('error', 'Guardian not found.');
            }

            return view('frontend.step4', compact('user'));
        }

        return redirect()->route('student.application');
    }


    public function stepFourStore(Request $request, $id)
    {
        $request->validate([
            'total_students' => 'required|integer|min:1|max:10',
        ]);

        $guardian = Guardiant::findOrFail($id);

        $guardian->update([
            'total_students' => $request->total_students,
        ]);

        return redirect()->route('step5');
    }




    public function step5()
    {
        $guardianId = session('guardian_id');

        if (!$guardianId) {
            return redirect()->route('student.application');
        }

        $guardian = Guardiant::find($guardianId);

        if (!$guardian) {
            return redirect()->route('student.application')->with('error', 'Guardian not found.');
        }

        $students = $guardian->students()->orderBy('id')->get();
        $totalStudents = $guardian->total_students ?? 0;

        // If session still exists, try to get the existing student by session serial
        if (session()->has('student_serial')) {
            $student = $students->firstWhere('student_serial', session('student_serial'));

            // If that student exists (already started but not completed), return it
            if ($student) {
                return view('frontend.step5', [
                    'serialPosition' => numberToOrdinal($student->student_serial),
                    'student' => $student,
                ]);
            }
        }

        // No session or student not found: find first incomplete
        $incompleteStudent = $students->first(function ($student) {
            return empty($student->first_name) ||
                empty($student->last_name) ||
                empty($student->dob) ||
                empty($student->country) ||
                empty($student->start_date);
        });

        if ($incompleteStudent) {
            $student = $incompleteStudent;
            $serialNumber = $student->student_serial ?? ($students->whereNotNull('student_serial')->count() + 1);
        } else {
            // All completed or session destroyed, make new
            $serialNumber = $students->count() + 1;

            if ($serialNumber > $totalStudents) {
                $serialNumber = $totalStudents;
            }

            $student = new \App\Models\Student();
            $student->student_serial = $serialNumber;
        }

        // Store or update session serial
        session(['student_serial' => $student->student_serial]);

        return view('frontend.step5', [
            'serialPosition' => numberToOrdinal($student->student_serial),
            'student' => $student,
        ]);
    }


    public function updateStudent(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'dob'         => 'required|date',
            'country'     => 'required|string|max:100',
            'start_date'  => 'required|date',
        ]);

        $guardianId = session('guardian_id');
        $serial = session('student_serial');

        if (!$guardianId) {
            return redirect()->route('student.application')->with('error', 'Session expired. Please start again.');
        }

        // Find the student belonging to this guardian by serial
        $student = \App\Models\Student::where('user_id', $guardianId)
            ->where('student_serial', $serial)
            ->first();

        if ($student) {
            // Update existing student
            $student->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'country' => $request->country,
                'start_date' => $request->start_date,
            ]);
        } else {
            // Create new student
            $maxSerial = \App\Models\Student::where('user_id', $guardianId)->max('student_serial');
            $newSerial = $maxSerial ? $maxSerial + 1 : 1;

            $student = \App\Models\Student::create([
                'user_id' => $guardianId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'dob' => $request->dob,
                'country' => $request->country,
                'start_date' => $request->start_date,
                'student_serial' => $newSerial,
            ]);

            // Save new serial to session
            session(['student_serial' => $student->student_serial]);
        }

        return redirect()->route('step6')->with('success', 'Student information saved.');
    }



    // public function updateStudent(Request $request, $id)
    // {
    //     $request->validate([
    //         'first_name'  => 'required|string|max:255',
    //         'last_name'   => 'required|string|max:255',
    //         'dob'         => 'required|date',
    //         'country'     => 'required|string|max:100',
    //         'start_date'  => 'required|date',
    //     ]);

    //     $student = Student::updateOrCreate(
    //         ['id' => $id, 'user_id' => auth()->id()],
    //         [
    //             'first_name'   => $request->first_name,
    //             'last_name'    => $request->last_name,
    //             'dob'          => $request->dob,
    //             'country'      => $request->country,
    //             'start_date'   => $request->start_date,
    //         ]
    //     );

    //     return redirect()->route('step6')->with('success', 'Student information updated.');
    // }
    // public function updateStudent(Request $request)
    // {
    //     $request->validate([
    //         'first_name'  => 'required|string|max:255',
    //         'last_name'   => 'required|string|max:255',
    //         'dob'         => 'required|date',
    //         'country'     => 'required|string|max:100',
    //         'start_date'  => 'required|date',
    //     ]);


    //     $maxSerial = Student::where('user_id', auth()->id())->max('student_serial');
    //     $newSerial = $maxSerial ? $maxSerial + 1 : 1;

    //     $newStudent = Student::create([
    //         'user_id' => auth()->id(),
    //         'first_name' => $request->first_name,
    //         'last_name' => $request->last_name,
    //         'dob' => $request->dob,
    //         'country' => $request->country,
    //         'start_date' => $request->start_date,
    //         'student_serial' => $newSerial,
    //     ]);

    //     session(['student_serial' => $newStudent->student_serial]);


    //     return redirect()->route('step6')->with('success', 'Student information updated.');
    // }



    public function step6()
    {
        $guardianId = session('guardian_id');

        if (!$guardianId) {
            return redirect()->route('student.application');
        }

        // Get GroupYear data filtered by guardian's year_group_id
        $data = GroupYear::where('status', 1)
            ->where('group_id', Guardiant::find($guardianId)->year_group_id)
            ->get();

        $serial = session('student_serial');

        if (!$serial) {
            return redirect()->route('step5')->with('error', 'Student serial missing.');
        }

        $student = Student::where('student_serial', $serial)
            ->where('user_id', $guardianId)
            ->first();

        return view('frontend.step6', compact('data', 'serial', 'student'));
    }



    public function submitStep6(Request $request)
    {
        $request->validate([
            'group_year_id' => 'required',
            'selected_year' => 'required|string|max:255',
        ]);

        $guardianId = session('guardian_id');
        $studentSerial = $request->student_serial;

        if (!$guardianId) {
            return redirect()->route('student.application')->with('error', 'Session expired. Please start again.');
        }

        $student = Student::where('user_id', $guardianId)
            ->where('student_serial', $studentSerial)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student profile not found.');
        }

        $student->group_year_id = $request->group_year_id;
        $student->selected_year  = $request->selected_year;
        $student->save();

        session(['group_year_id' => $request->group_year_id]);

        return redirect()->route('step7')->with('success', 'Year group updated!');
    }


    public function step7()
    {
        $guardianId = session('guardian_id');

        if (!$guardianId) {
            return redirect()->route('student.application');
        }

        $groupYearId = session('group_year_id');
        $serial = session('student_serial');

        if (!$groupYearId || !$serial) {
            return redirect()->route('step6')->with('error', 'Group Year ID or Student Serial not found.');
        }

        $qualifications = Qualification::where('group_year_id', $groupYearId)
            ->where('status', 1)
            ->get();

        // Get student by serial and guardian ID
        $student = Student::where('student_serial', $serial)
            ->where('user_id', $guardianId)
            ->first();

        return view('frontend.step7', compact('qualifications', 'serial', 'student'));
    }





    public function submitStep7(Request $request)
    {
        $request->validate([
            'qualification_id' => 'required',
        ]);

        $guardianId = session('guardian_id');
        $studentSerial = $request->student_serial;

        if (!$guardianId) {
            return redirect()->route('student.application')->with('error', 'Session expired. Please start again.');
        }

        $student = Student::where('user_id', $guardianId)
            ->where('student_serial', $studentSerial)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student profile not found.');
        }

        $student->qualification_id = $request->qualification_id;
        $student->save();

        return redirect()->route('step8')->with('success', 'Qualification updated!');
    }



    public function step8()
    {
        $guardianId = session('guardian_id');

        if (!$guardianId) {
            return redirect()->route('student.application');
        }

        $serial = session('student_serial');

        // Find the student with related qualification and subjects
        $student = Student::where('user_id', $guardianId)
            ->where('student_serial', $serial)
            ->with('qualification.coreSubjects', 'qualification.additionalSubjects', 'qualification.additionalIslamic', 'qualification.additionalLanguages')
            ->first();

        if (!$student || !$student->qualification) {
            return redirect()->back()->with('error', 'Qualification not found.');
        }

        $qualification = $student->qualification;

        return view('frontend.step8', compact('serial', 'qualification', 'student'));
    }


    protected function extractIds(array $items, string $prefix)
    {
        return array_map(function ($item) use ($prefix) {
            return (int) str_replace($prefix, '', $item);
        }, $items);
    }


    public function updateSelection(Request $request)
    {
        $guardianId = session('guardian_id');

        if (!$guardianId) {
            return redirect()->route('student.application');
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'core_subjects' => 'required|json',
            'additional_subjects' => 'nullable|json',
            'languages' => 'nullable|json',
            'islamic_subjects' => 'nullable|json',
            'hifdh_programmes' => 'nullable|json',
        ]);

        $student = Student::findOrFail($request->student_id);

        $coreSubjects = $this->extractIds(json_decode($request->core_subjects, true), 'core-');
        $additionalSubjects = $this->extractIds(json_decode($request->additional_subjects ?? '[]', true), 'sub-');
        $additionalIslamic = $this->extractIds(json_decode($request->islamic_subjects ?? '[]', true), 'islamic-');
        $languages = $this->extractIds(json_decode($request->languages ?? '[]', true), 'lang-');
        $additionalHifdh = $this->extractIds(json_decode($request->hifdh_programmes ?? '[]', true), 'hifdh-');

        $student->coreSubjects()->sync($coreSubjects);
        $student->additionalSubjects()->sync($additionalSubjects);
        $student->additionalIslamic()->sync($additionalIslamic);
        $student->additionalLanguages()->sync($languages);
        $student->additionalHifdh()->sync($additionalHifdh);

        session()->forget('student_serial');

        $authUser = Guardiant::findOrFail($guardianId);

        if ($student->user_id === $authUser->id && $student->student_serial == $authUser->total_students) {
            return redirect()->route('step9')->with('success', 'All students completed. Moving to next step.');
        }

        return redirect()->route('step5')->with('success', 'Subjects saved and session reset.');
    }




    public function step9()
    {
        $guardianId = session('guardian_id');

        if ($guardianId) {
            // ✅ Get full Guardiant model from DB
            $guardian = Guardiant::with('courseFee', 'students')->find($guardianId);

            if (!$guardian) {
                return redirect()->route('student.application');
            }

            $userIds = $guardian->students->pluck('user_id')->unique();
            // $lastSerial = session('last_completed_student_serial');

            $students = Student::whereIn('user_id', $userIds)
                ->with([
                    'qualification.coreSubjects',
                    'qualification.additionalSubjects',
                    'qualification.additionalIslamic',
                    'qualification.additionalLanguages',
                    'user.courseFee'
                ])
                ->get();

            $setting = Setting::first();

            return view('frontend.step9', compact('students', 'guardian', 'setting'));
        }

        return redirect()->route('student.application');
    }




    // public function step9()
    // {
    //     if (Auth::check()) {
    //         $students = Student::where('user_id', auth()->id())
    //             ->with([
    //                 'qualification.coreSubjects',
    //                 'qualification.additionalSubjects',
    //                 'qualification.additionalIslamic',
    //                 'qualification.additionalLanguages',
    //                 'user.courseFee' // eager load CourseFee via user
    //             ])
    //             ->get();

    //         return view('frontend.step9', compact('students'));
    //     }

    //     return redirect()->route('student.application');
    // }







    public function autoRegister(Request $request)
    {
        $request->validate([
            'year_group_id' => 'required',
            'package_id' => 'required',
            'plan_id' => 'required',
            'course_fee_id' => 'required',
        ]);

        session()->forget([
            'guardian_id',
            'student_serial',
            'last_completed_student_serial',
            // add more keys if needed
        ]);

        // ✅ Create the Guardian (assuming this is the primary model now)
        $guardian = Guardiant::create([
            'year_group_id' => $request->year_group_id,
            'package_id' => $request->package_id,
            'plan_id' => $request->plan_id,
            'course_fee_id' => $request->course_fee_id,
        ]);

        // ✅ Store Guardian ID in session
        session(['guardian_id' => $guardian->id]);

        return response()->json([
            'success' => true,
            'redirect' => route('step1') // You can access session in step1 now
        ]);
    }




    // api

    public function getPackages($groupId)
    {
        return response()->json(Package::where('group_id', $groupId)->get());
    }

    // public function getPlans($packageId)
    // {
    //     return response()->json(Plan::where('group_id', $packageId)->where('status', 1)->get());
    // }

    public function getPlans($packageId)
    {
        $package = Package::find($packageId);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $plans = Plan::where('group_id', $package->group_id)
            ->where('status', 1)
            ->get();

        return response()->json($plans);
    }

    public function getCourseFees($groupId)
    {
        return response()->json(CourseFee::where('group_year_id', $groupId)->get());
    }


    public function studentInfo($id)
    {
        $data = Student::findOrFail($id);
        return view('frontend.student-update', compact('data'));
    }
    public function parentUpdate($id)
    {
        $data = Guardiant::findOrFail($id);
        return view('frontend.parent-update', compact('data'));
    }

    public function ParentInfoUpdate(Request $request, $id)
    {

        $request->validate([
            'title' => 'required|string|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'confirm' => 'required|in:Father,Mother,Guardian',
        ]);

        $user = Guardiant::findOrFail($id);

        $user->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'confirm' => $request->confirm,
        ]);

        return redirect()->route('step9');
    }

    public function studentInfoUpdate(Request $request, $id)
    {
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'dob'         => 'required|date',
            'country'     => 'required|string|max:100',
            'start_date'  => 'required|date',
        ]);

        $student = Student::findOrFail($id);


        $student->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'dob' => $request->dob,
            'country' => $request->country,
            'start_date' => $request->start_date,
        ]);

        return redirect()->route('step9')->with('success', 'Student information updated.');
    }

    // public function payNow(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required',
    //         'course_id' => 'required',
    //         'amount' => 'required',
    //     ]);


    //     Order::create([
    //         'user_id' => $request->user_id,
    //         'course_id' => $request->course_id,
    //         'amount' => $request->amount,
    //         'status' => 'pending',
    //     ]);

    //     Auth::logout();

    //     return redirect()->route('student.application')->with('success', 'Order placed successfully. Please log in again to continue.');
    // }



    public function payNow(Request $request)
    {

        $request->validate([
            'user_id' => 'required|',
            'course_id' => 'required|',
            'amount' => 'required|numeric',
            'payment_method' => 'required|in:stripe,offline',
        ]);

        $paymentMethod = $request->payment_method;

        if ($paymentMethod === 'offline') {
            // ✅ Save order with status 'pending'
            $order = Order::create([
                'user_id' => $request->user_id,
                'course_id' => $request->course_id,
                'amount' => $request->amount,
                'coupon' => $request->coupon,
                'stripe_transaction_id' => null,
                'card_holder_name' => $request->email,
                'card_last4' => null,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // ✅ Send email
            try {
                Mail::to($order->user->email)->send(new PaymentInvoiceMail($order));
                Mail::to('alrushd.uk@gmail.com')->send(new PaymentInvoiceMail($order));
            } catch (\Exception $e) {
                \Log::error('❌ Offline Payment email failed: ' . $e->getMessage());
            }

            session()->flush();

            return redirect()->route('payment.success.page')->with('success', 'Offline payment request submitted and invoice sent.');
        } else {

            Stripe::setApiKey(config('services.stripe.secret'));

            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => 'Course Enrollment Fee',
                        ],
                        'unit_amount' => $request->amount * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $request->email ?? null,
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'user_id' => $request->user_id,
                    'course_id' => $request->course_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'coupon' => $request->coupon,
                ],
            ]);

            return redirect($checkoutSession->url);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $session_id = $request->get('session_id');

        if (!$session_id) {
            return redirect()->route('step9')->with('error', 'Invalid session.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        $metadata = $session->metadata;


        // ✅ PaymentIntent retrieve
        $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

        // ✅ First charge object
        $charge = $paymentIntent->charges->data[0] ?? null;

        $card = $charge->payment_method_details->card ?? null;
        $card_last4 = $card->last4 ?? '****';
        $card_brand = $card->brand ?? 'N/A';
        $authorization_code = $card->authorization_code ?? 'Unavailable';

        // ✅ Merchant info (your own Stripe account)
        $account = \Stripe\Account::retrieve();
        $merchant_name = $account->business_profile->name ?? 'Unknown';



        // ✅ Save order
        $order = Order::create([
            'user_id' => $metadata->user_id,
            'course_id' => $metadata->course_id,
            'amount' => $metadata->amount,
            'coupon' => $metadata->coupon,
            'stripe_transaction_id' => $session->payment_intent,
            'card_holder_name' => $session->customer_email,
            'card_last4' => $card_brand . ' - ' . $card_last4,
            'status' => 'paid',
            'payment_method' => $metadata->payment_method,
            'authorization_code' => $authorization_code,
            'merchant_name' => $merchant_name,

        ]);

        // ✅ Send email
        try {
            Mail::to($order->user->email)->send(new PaymentInvoiceMail($order));
            // Mail::to('kamrulislamraju061@gmail.com')->send(new PaymentInvoiceMail($order));
            // Mail::to('alrushd.uk@gmail.com')->send(new PaymentInvoiceMail($order));
        } catch (\Exception $e) {
            \Log::error('❌ Payment email failed: ' . $e->getMessage());
        }

        // ✅ Logout & clear session
        // Auth::guard('web')->logout();
        session()->flush();

        // ✅ Redirect to success page
        return redirect()->route('payment.success.page')->with('success', 'Payment completed and email sent.');
    }



    public function paymentCancel()
    {
        return redirect()->route('step9')->with('error', 'Payment cancelled.');
    }

    public function applyCoupon(Request $request)
    {
        $coupon = Coupon::where('name', $request->coupon)->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found!'
            ]);
        }

        $now = Carbon::now();

        if ($coupon->start_date && $coupon->start_date > $now) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon is not active yet!'
            ]);
        }

        if ($coupon->end_date && $coupon->end_date < $now) {
            return response()->json([
                'status' => false,
                'message' => 'Coupon has expired!'
            ]);
        }

        return response()->json([
            'status' => true,
            'discount_percentage' => $coupon->discount_percentage,
            'message' => 'Coupon applied successfully!'
        ]);
    }
}


