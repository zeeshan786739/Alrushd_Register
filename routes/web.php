<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Api\FrontendFormDataController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MultiStepFormController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Mail\PaymentInvoiceMail;
use App\Mail\PaymentSuccessMail;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


      
 
Route::get('/cmd',function(){
    Artisan::call('storage:link');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return 'Done';

});

Route::get('/email-test',function(){
     // Dummy order অথবা latest order data
    // $order = Order::latest()->first();
    $order = FormSubmission::latest()->first();
    // $order = FormSubmission::find(32);
    try {
        // Mail::to('rajusheikh061@gmail.com')->send(new PaymentInvoiceMail($order));
        Mail::to('rajusheikh061@gmail.com')->send(new PaymentSuccessMail($order));
        return '✅ Test email sent successfully.';
    } catch (\Exception $e) {
        \Log::error('Email sending failed: ' . $e->getMessage());
        return '❌ Failed to send email. Check log for details.';
    }

});


Route::get('/student-admission', fn () => redirect('/forms/student-admission', 301));
Route::get('/student-admission/step/{step}', fn () => redirect('/forms/student-admission', 301))->name('form.step');
Route::post('/student-admission/step/{step}', [MultiStepFormController::class, 'postStep'])->name('form.step.post');


Route::get('/payment-success', function (\Illuminate\Http\Request $request) {
    if ($request->has('session_id')) {
        return app(FrontendController::class)->paymentSuccess($request);
    }

    return view('student.payment-confirm');
})->name('payment.success');

Route::get('/payment-cancel', [FrontendController::class, 'paymentCancel'])->name('payment.cancel');

// Public email marketing tracking / unsubscribe (opaque tokens only — no auth)
Route::get('/email-marketing/track/open/{token}', [\App\Http\Controllers\EmailMarketing\PublicTrackingController::class, 'open'])
    ->middleware('throttle:120,1')
    ->name('email-marketing.track.open');
Route::get('/email-marketing/track/click/{token}', [\App\Http\Controllers\EmailMarketing\PublicTrackingController::class, 'click'])
    ->middleware('throttle:120,1')
    ->name('email-marketing.track.click');
Route::get('/email-marketing/unsubscribe/{token}', [\App\Http\Controllers\EmailMarketing\PublicTrackingController::class, 'unsubscribeShow'])
    ->middleware('throttle:30,1')
    ->name('email-marketing.unsubscribe.show');
Route::post('/email-marketing/unsubscribe/{token}', [\App\Http\Controllers\EmailMarketing\PublicTrackingController::class, 'unsubscribeStore'])
    ->middleware('throttle:30,1')
    ->name('email-marketing.unsubscribe.store');

Route::get('/parents-update/{id}', [MultiStepFormController::class, 'parentUpdate'])->name('parent-update');
Route::put('/parents-update/{id}', [MultiStepFormController::class, 'parentUpdateData'])->name('parents-update');

Route::get('/students-update/{id}', [MultiStepFormController::class, 'studentUpdate'])->name('students.update');
Route::put('/students-update/{id}', [MultiStepFormController::class, 'studentUpdateData'])->name('parents-update.form');

Route::get('/get-years/{group}', [MultiStepFormController::class, 'getYears']);
// Route::get('/get-packages/{group}/{year}', [MultiStepFormController::class, 'getPackages']);
Route::get('/get-packages/{year}', [MultiStepFormController::class, 'getPackages']);
Route::get('/get-course-details', [MultiStepFormController::class, 'getCourseDetails']); // AJAX subjects + hifdh

Route::get('/get-packages/{groupId}', [FrontendController::class, 'getPackages']);
Route::get('/get-plans/{packageId}', [FrontendController::class, 'getPlans']);
Route::get('/get-course-fees/{groupId}', [FrontendController::class, 'getCourseFees']);
Route::post('/apply-coupon', [FrontendController::class, 'applyCoupon'])->name('apply.coupon');



Route::get('/',[FrontendController::class,'index'])->name('index');

Route::get('/forms/{slug}', [FrontendController::class, 'dynamicForm'])->name('dynamic-form');
Route::get('/forms/{slug}/success', [FrontendController::class, 'dynamicFormSuccess'])->name('dynamic-form.success');

Route::get('/Forms/{slug}', fn (string $slug) => redirect('/forms/'.strtolower($slug), 301));
Route::get('/Forms/{slug}/success', fn (string $slug) => redirect('/forms/'.strtolower($slug).'/success', 301));

Route::get('/api/frontend/csrf', [FrontendFormDataController::class, 'csrf']);

Route::get('/api/frontend/forms', [\App\Http\Controllers\Api\DynamicFormController::class, 'index']);
Route::get('/api/frontend/forms/{slug}', [\App\Http\Controllers\Api\DynamicFormController::class, 'show']);
Route::post('/api/frontend/forms/{slug}/submit', [\App\Http\Controllers\Api\DynamicFormController::class, 'submit']);

Route::get('/calendly-events', [FrontendController::class, 'fetchEvents']);

Route::get('/book-a-call',[FrontendController::class,'bookCall'])->name('book-a-call');
Route::get('/enquire-now',[FrontendController::class,'enquireNow'])->name('enquire-now');
Route::get('/referral',[FrontendController::class,'referral'])->name('referral');


Route::get('/contact',[FrontendController::class,'contact'])->name('contact');

Route::post('/meeting/store', [FrontendController::class, 'mettingStore'])->name('meeting.store');
Route::post('/enquire/store', [FrontendController::class, 'enquireStore'])->name('enquire.store');
Route::post('/referral/store', [FrontendController::class, 'referralStore'])->name('referral.store');

Route::get('/open-event', [FrontendController::class, 'openEvent'])->name('open-event');
Route::get('/open-event-form/{id}', [FrontendController::class, 'openEventForm'])->name('open-event-form');
Route::post('/event-store', [FrontendController::class, 'openEventFormStore'])->name('event-store');
Route::get('/event-form-submission',function(){
    return view('frontend.openeventsuccess');
})->name('open-event-success');


Route::get('debit-form',[FrontendController::class,'debitForm'])->name('debit.form');
Route::post('debit/store',[FrontendController::class,'debitFormStore']);
Route::get('debit/success',[FrontendController::class,'debitSubmissionSuccwss'])->name('debit.success');


Route::get('/staff-application',[FrontendController::class,'staffAdmissionForm'])->name('staff-admission');
Route::get('/job-applications',[FrontendController::class,'jobAdmissionForm'])->name('job-applications');
Route::get('/job-applications-success', [FrontendController::class, 'jobApplicationsSuccess'])->name('job-applications.success');

Route::get('/staff-application-success', [FrontendController::class, 'staffApplicationsSuccess'])->name('staff-applications.success');


Route::post('/staff-applications-form',[FrontendController::class,'staffAdmissionFormStore']);
Route::post('/job-applications-form',[FrontendController::class,'jobAdmissionFormStore']);




Route::get('/student-application',[FrontendController::class,'studentApplication'])->name('student.application');
Route::get('/step1',[FrontendController::class,'step1'])->name('step1');
Route::get('/step2',[FrontendController::class,'step2'])->name('step2');
Route::get('/step3',[FrontendController::class,'step3'])->name('step3');
Route::get('/step4',[FrontendController::class,'step4'])->name('step4');
Route::get('/step5',[FrontendController::class,'step5'])->name('step5');
Route::get('/step6',[FrontendController::class,'step6'])->name('step6');
Route::get('/step7',[FrontendController::class,'step7'])->name('step7');
Route::get('/step8',[FrontendController::class,'step8'])->name('step8');
Route::get('/step9',[FrontendController::class,'step9'])->name('step9');

// routes/web.php
Route::post('/auto-register-fee-user', [FrontendController::class, 'autoRegister'])->name('auto.register.fee.user');
Route::put('/step-one-store/{id}', [FrontendController::class, 'stepOneStore'])->name('step_one.store');
Route::put('/step-two-store/{id}', [FrontendController::class, 'stepTwoStore'])->name('step_two.store');
Route::put('/step-two-three-store/{id}', [FrontendController::class, 'updateTimetableForm'])->name('user.updateTimetableForm');

Route::put('/step-four/{id}', [FrontendController::class, 'stepFourStore'])->name('step_four.store');
Route::put('/student/update', [FrontendController::class, 'updateStudent'])->name('student.update');
Route::post('/step6-submit', [FrontendController::class, 'submitStep6'])->name('step6.submit');
Route::post('/step7-submit', [FrontendController::class, 'submitStep7'])->name('step7.submit');
Route::post('/subjects/update-selection', [FrontendController::class, 'updateSelection'])->name('subjects.updateSelection');
Route::get('parents/update/{id}',[FrontendController::class,'parentUpdate'])->name('parents.update');
Route::put('/parents-info/update/{id}', [FrontendController::class, 'ParentInfoUpdate'])->name('patents-info.update');
Route::get('student-info-update/{id}',[FrontendController::class,'studentInfo'])->name('studetn-info-update');
Route::put('/student-information-update/{id}', [FrontendController::class, 'studentInfoUpdate'])->name('student-information.update');
Route::post('/pay-now', [FrontendController::class, 'payNow'])->name('pay.now');
// Route::get('/payment-success', [FrontendController::class, 'paymentSuccess'])->name('payment.success');
// Route::get('/payment-cancel', [FrontendController::class, 'paymentCancel'])->name('payment.cancel');

Route::get('/payment-success-page', function () {
    Auth::guard('web')->logout();
    session()->flush();
    return view('payment_success'); // Custom HTML view
})->name('payment.success.page');





Route::redirect('/login', '/admin/login', 301);
Route::redirect('/register', '/admin/login', 301);

Route::get('/home',function(){

    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }else{
        return redirect()->route('admin.login');
    }
    
});


Route::post('/user-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/')->with('success', 'You are logged out.');
})->name('user.logout');


// Disable default Laravel UI login/register — admin auth is at /admin/login
Auth::routes(['login' => false, 'register' => false]);

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

require __DIR__.'/admin-auth.php';



// php artisan migrate:refresh --path=database/migrations/2025_09_28_044940_create_form_submissions_table.php

// php artisan make:migration create_core_subject_student_table --create=core_subject_student
// php artisan make:migration create_additional_subject_student_table --create=additional_subject_student
// php artisan make:migration create_language_student_table --create=language_student


