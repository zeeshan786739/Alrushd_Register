<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdmissiondateController;
use App\Http\Controllers\Admin\AdmissionStudentController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\CourseFeeController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\GroupYearController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\QualificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WebsiteCmsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\DebitController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquireController;
use App\Http\Controllers\Admin\FormManagerController;
use App\Http\Controllers\Admin\GenderController;
use App\Http\Controllers\Admin\MeetSpeakerController;
use App\Http\Controllers\Admin\MettingFormController;
use App\Http\Controllers\Admin\NationalityController;
use App\Http\Controllers\Admin\OpenEventController;
use App\Http\Controllers\Admin\OpenEventItemController;
use App\Http\Controllers\Admin\OpenEventSubmissionFormController;
use App\Http\Controllers\Admin\PaymentCountryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\RelationShipController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\TimeTableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StaffApplicationController;
use App\Http\Controllers\Admin\StudentCourseController;
use App\Http\Controllers\Admin\StudentFormController;
use App\Http\Controllers\Admin\StudentGroupController;
use App\Http\Controllers\Admin\StudentLanguageController;
use App\Http\Controllers\Admin\StudentPackageController;
use App\Http\Controllers\Admin\StudentSubjectController;
use App\Http\Controllers\Admin\StudentYearController;
use App\Http\Controllers\Admin\TermsAndConditionController;
use App\Http\Controllers\Admin\WordPressApiController;
use App\Http\Controllers\Admin\WpApiController;
use App\Models\StudentSubject;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('guest:admin')->group(function () {

    Route::get('register', [RegisterController::class, 'create'])->name('admin.register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', [LoginController::class, 'create'])->name('admin.login');
    Route::post('login', [LoginController::class, 'store']);

});

Route::prefix('admin')->name('admin.')
// ->middleware('auth:admin')
->middleware(['auth:admin', 'admin.only', 'admin.has.role'])
->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');


    Route::get('/profile/settings', [AdminProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profile/settings', [AdminProfileController::class, 'updateSettings'])->name('profile.settings.update');

    Route::get('/change-password', [AdminProfileController::class, 'changePassword'])->name('change.password');
    Route::put('/change-password', [AdminProfileController::class, 'updatePassword'])->name('change.password.update');

    Route::get('settings', [WebsiteCmsController::class, 'index'])->name('settings.index');
    Route::prefix('website-cms')->name('website-cms.')->group(function () {
        Route::post('draft', [WebsiteCmsController::class, 'saveDraft'])->name('draft');
        Route::post('publish', [WebsiteCmsController::class, 'publish'])->name('publish');
        Route::post('discard', [WebsiteCmsController::class, 'discard'])->name('discard');
        Route::post('reset-section', [WebsiteCmsController::class, 'resetSection'])->name('reset-section');
        Route::post('preview', [WebsiteCmsController::class, 'preview'])->name('preview');
        Route::post('upload', [WebsiteCmsController::class, 'upload'])->name('upload');
        Route::post('restore-version', [WebsiteCmsController::class, 'restoreVersion'])->name('restore-version');
    });

    Route::resource('settings', SettingController::class)->except(['index']);

    // Role Management
    Route::resource('roles',RoleController::class);
    Route::resource('permissions',PermissionController::class);
    Route::resource('users',UserController::class);

    // Group
    Route::resource('coupons',CuponController::class);
    Route::resource('time-tables',TimeTableController::class);

    // Form Center — dynamic form builder & submissions
    Route::get('form-center', [FormManagerController::class, 'index'])->name('form-manager.index');
    Route::get('form-center/option-sources/{source}', [FormManagerController::class, 'optionSourceValues'])->name('form-manager.option-source');
    Route::get('form-center/create', [FormManagerController::class, 'create'])->name('form-manager.create');
    Route::post('form-center', [FormManagerController::class, 'store'])->name('form-manager.store');
    Route::get('form-center/{form}/edit', [FormManagerController::class, 'edit'])->name('form-manager.edit');
    Route::put('form-center/{form}', [FormManagerController::class, 'update'])->name('form-manager.update');
    Route::delete('form-center/{form}', [FormManagerController::class, 'destroy'])->name('form-manager.destroy');
    Route::post('form-center/{form}/toggle', [FormManagerController::class, 'toggleActive'])->name('form-manager.toggle');
    Route::post('form-center/{form}/toggle-placement', [FormManagerController::class, 'togglePlacement'])->name('form-manager.toggle-placement');
    Route::post('form-center/{form}/toggle-landing', [FormManagerController::class, 'toggleLanding'])->name('form-manager.toggle-landing');
    Route::put('form-center/{form}/settings', [FormManagerController::class, 'updateSettings'])->name('form-manager.settings');
    Route::post('form-center/{form}/duplicate', [FormManagerController::class, 'duplicate'])->name('form-manager.duplicate');
    Route::get('form-center/{form}/entries', [FormManagerController::class, 'entries'])->name('form-manager.entries');
    Route::get('form-center/{form}/entries/{entry}', [FormManagerController::class, 'entryShow'])->name('form-manager.entries.show');
    Route::patch('form-center/{form}/entries/{entry}/status', [FormManagerController::class, 'entryUpdateStatus'])->name('form-manager.entries.status');
    Route::delete('form-center/{form}/entries/{entry}', [FormManagerController::class, 'entryDestroy'])->name('form-manager.entries.destroy');

    // Form Submission
    Route::resource('staff-applications-form',StaffApplicationController::class);
    Route::post('/staff-applications-form/status-update', [StaffApplicationController::class, 'updateStatus'])->name('staff-applications-form.status.update');
    // Route::get('job-applications-form',[StaffApplicationController::class,'job'])->name('job-applications-form.index');
     
     
    Route::get('job-applications-form',[StaffApplicationController::class,'job'])->name('job-applications-form.index');
    Route::get('job-applications-form/{id}',[StaffApplicationController::class,'jobView'])->name('job-applications-form.view');
    Route::delete('job-applications-form-delete/{id}',[StaffApplicationController::class,'jobDelete'])->name('job-applications-form.destroy');

  
    // Metting
    Route::resource('metting-form',MettingFormController::class);

    // Debit Forms
    Route::resource('debit-forms',DebitController::class);

    // Enquire
    Route::resource('enquires',EnquireController::class);
    Route::delete('enquires/delete/{id}',[EnquireController::class,'destroy'])->name('enquires.delete');
    // Referral
    Route::resource('referrals',ReferralController::class);
    Route::delete('referrals/delete/{id}',[EnquireController::class,'destroy'])->name('referrals.delete');



    Route::resource('groups',GroupController::class);
    Route::resource('package',PackageController::class);
    Route::get('/group/{group_id}/packages', [PackageController::class, 'getPackagesByGroup']);


    Route::resource('plans',PlanController::class);

    Route::resource('languages',LanguageController::class);
    Route::resource('subjects',SubjectController::class);
    Route::resource('group-years',GroupYearController::class);
    
    Route::resource('qualifications',QualificationController::class);
    Route::get('/group-year/{id}/qualifications', [QualificationController::class, 'getQualificationsByGroupYear']);
    


    Route::resource('course-fees',CourseFeeController::class);

    Route::resource('admission-student-list',AdmissionStudentController::class);


    // Route::get('job-applications',[WordPressApiController::class,'jobApplication'])->name('job-applications');
    // Route::get('/job-applications/view/{id}', [WordPressApiController::class, 'jobApplicationView'])->name('job.application.view');
    Route::get('staff-applications',[WordPressApiController::class,'staffApplication'])->name('staff-applications');
    Route::get('staff-applications/view/{index}', [WordPressApiController::class, 'staffApplicationView'])->name('staff-applications.view');
    // Route::get('apply-now',[WordPressApiController::class,'applyNow'])->name('apply-now');
    Route::get('apply-now/view/{index}', [WordPressApiController::class, 'applyNowView'])->name('apply-now.view');
    // Route::get('online-madrasah',[WordPressApiController::class,'onlineMadrasah'])->name('online-madrasah');
    Route::get('online-madrasah/view/{index}', [WordPressApiController::class, 'onlineMadrasahView'])->name('online-madrasah.view');
    // Route::get('subscribe-applications',[WordPressApiController::class,'subscribeApplication'])->name('subscribe-applications');
    // Route::get('enquire-now',[WordPressApiController::class,'enquireNow'])->name('enquire-now');
    Route::get('enquire-now/view/{index}', [WordPressApiController::class, 'enquireNowView'])->name('enquire-now.view');
    // Route::get('referral-applications',[WordPressApiController::class,'referralApplication'])->name('referral-applications');
    Route::get('referral-applications/view/{index}', [WordPressApiController::class, 'referralApplicationView'])->name('referral-applications.view');

    // Api WP

    Route::get('job-applications',[WpApiController::class,'jobApplications'])->name('job-applications');
    Route::get('job-applications/{entry_id}',[WpApiController::class,'jobApplicationView'])->name('job.application.view');
    Route::get('/form-import-job-applications/{form_id}', [WpApiController::class, 'importJobApplication'])->name('form.import.jobapplication');


    Route::get('/form-import/{id}', [WpApiController::class, 'import'])->name('form.import');
    Route::get('apply-now',[WpApiController::class,'applyNow'])->name('apply-now');
    Route::get('student-admission-view/{entry_id}',[WpApiController::class,'studentAdmissionView'])->name('student-admission-view');
    Route::get('online-madrasah',[WpApiController::class,'onlineMadrasah'])->name('online-madrasah');



    Route::get('form-entries-view/{entry_id}',[WpApiController::class,'fornView'])->name('form-entries.view');
    
    Route::get('subscribe-applications',[WpApiController::class,'subscribeApplications'])->name('subscribe-applications');
    Route::get('/form-import-subscription/{id}', [WpApiController::class, 'importSubscription'])->name('form.import.subscription');


    Route::get('enquire-now',[WpApiController::class,'enquireNow'])->name('enquire-now');
    Route::get('enquire-now/{entry_id}',[WpApiController::class,'enquirenowView'])->name('enquire-now-view');
    Route::get('referral-applications',[WpApiController::class,'referralApplications'])->name('referral-applications');
    Route::get('referral-now/{entry_id}',[WpApiController::class,'referralnowView'])->name('referral-now-view');
    Route::get('direct-debit',[WpApiController::class,'directDebit'])->name('direct-debit');
    Route::get('debit-now/{entry_id}',[WpApiController::class,'debitnowView'])->name('debit-now-view');
    Route::get('/form-import-contact/{id}', [WpApiController::class, 'importContact'])->name('form.import.contact');
    

    // Open Event
    Route::resource('open-events',OpenEventController::class);
    Route::resource('open-event-items',OpenEventItemController::class);
    Route::resource('meet-speakers',MeetSpeakerController::class);
    Route::resource('open-event-form',OpenEventSubmissionFormController::class);


    // Student
    Route::resource('student-groups',StudentGroupController::class);
    Route::resource('student-years',StudentYearController::class);
    Route::resource('student-language',StudentLanguageController::class);
    Route::resource('student-subject',StudentSubjectController::class);
    Route::resource('student-package',StudentPackageController::class);
    Route::resource('student-course',StudentCourseController::class);
    Route::get('/get-years/{group_id}', [StudentCourseController::class, 'getYears']);
    Route::resource('form-students',StudentFormController::class);
    Route::put('form-students-single/{id}',[StudentFormController::class,'singleStudentUpdate'])->name('form-student-single.update');
    Route::get('/download-payment-pdf/{id}',[StudentFormController::class,'downloadPDF'])->name('download.payment.pdf');
    

    Route::resource('nationality',NationalityController::class);
    Route::resource('admission-date',AdmissiondateController::class);
    Route::resource('genders',GenderController::class);
    Route::resource('terms',TermsAndConditionController::class);
    Route::get('staff-terms-condition',[TermsAndConditionController::class,'staffTerms'])->name('staff-terms-condition');
    Route::resource('relation-ships',RelationShipController::class);
    Route::resource('countries',PaymentCountryController::class);

    Route::resource('student-school',SchoolController::class);

    






});