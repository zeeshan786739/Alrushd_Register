<?php

use App\Http\Controllers\Admin\Crm\CrmOverviewController;
use App\Http\Controllers\Admin\Crm\CustomerController;
use App\Http\Controllers\Admin\Crm\DocumentSettingsController;
use App\Http\Controllers\Admin\Crm\FormEntryController;
use App\Http\Controllers\Admin\Crm\InvoiceController;
use App\Http\Controllers\Admin\Crm\LeadController;
use App\Http\Controllers\Admin\Crm\LeadImportController;
use App\Http\Controllers\Admin\Crm\ProjectController;
use App\Http\Controllers\Admin\Crm\QuotationController;
use Illuminate\Support\Facades\Route;

Route::prefix('crm')->name('crm.')->group(function () {
    Route::get('/', [CrmOverviewController::class, 'index'])->name('overview');
    Route::get('settings/documents', [DocumentSettingsController::class, 'edit'])->name('settings.documents.edit');
    Route::put('settings/documents', [DocumentSettingsController::class, 'update'])->name('settings.documents.update');
    Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('leads/import', [LeadImportController::class, 'create'])->name('leads.import.create');
    Route::post('leads/import', [LeadImportController::class, 'store'])->name('leads.import.store');
    Route::get('leads/import/history', [LeadImportController::class, 'index'])->name('leads.import.index');
    Route::get('leads/import/{leadImport}/map', [LeadImportController::class, 'map'])->name('leads.import.map');
    Route::post('leads/import/{leadImport}/map', [LeadImportController::class, 'saveMap'])->name('leads.import.map.save');
    Route::get('leads/import/{leadImport}/preview', [LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('leads/import/{leadImport}/confirm', [LeadImportController::class, 'confirm'])->name('leads.import.confirm');
    Route::get('leads/import/{leadImport}/failed-rows', [LeadImportController::class, 'failedRows'])->name('leads.import.failed-rows');
    Route::get('leads/import/{leadImport}', [LeadImportController::class, 'show'])->name('leads.import.show');
    Route::post('leads/filters', [LeadController::class, 'saveFilter'])->name('leads.filters.save');
    Route::delete('leads/filters/clear', [LeadController::class, 'clearFilters'])->name('leads.filters.clear');
    Route::delete('leads/filters/{savedFilter}', [LeadController::class, 'destroyFilter'])->name('leads.filters.destroy');
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.notes.store');
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status.update');
    Route::patch('leads/{lead}/inline', [LeadController::class, 'inlineUpdate'])->name('leads.inline');
    Route::patch('leads/{lead}/follow-up', [LeadController::class, 'setFollowUp'])->name('leads.follow-up');
    Route::post('leads/{lead}/follow-up/complete', [LeadController::class, 'completeFollowUp'])->name('leads.follow-up.complete');
    Route::patch('leads/{lead}/appointment', [LeadController::class, 'setAppointment'])->name('leads.appointment');
    Route::patch('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::get('leads/{lead}/email', [LeadController::class, 'emailForm'])->name('leads.email.form');
    Route::post('leads/{lead}/email', [LeadController::class, 'sendEmail'])->name('leads.email.send');
    Route::resource('leads', LeadController::class);

    Route::post('customers/{customer}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
    Route::delete('customers/{customer}/contacts/{contact}', [CustomerController::class, 'destroyContact'])->name('customers.contacts.destroy');
    Route::post('customers/{customer}/activities', [CustomerController::class, 'storeActivity'])->name('customers.activities.store');
    Route::patch('customers/{customer}/inline', [CustomerController::class, 'inlineUpdate'])->name('customers.inline');
    Route::resource('customers', CustomerController::class);

    Route::post('projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [ProjectController::class, 'updateTask'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [ProjectController::class, 'destroyTask'])->name('projects.tasks.destroy');
    Route::patch('projects/{project}/inline', [ProjectController::class, 'inlineUpdate'])->name('projects.inline');
    Route::resource('projects', ProjectController::class);

    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/mark-sent', [QuotationController::class, 'markSent'])->name('quotations.mark-sent');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
    Route::resource('quotations', QuotationController::class);

    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::resource('invoices', InvoiceController::class);

    Route::get('form-submissions/export', [FormEntryController::class, 'export'])->name('form-entries.export');
    Route::get('form-submissions', [FormEntryController::class, 'index'])->name('form-entries.index');
    Route::get('forms/{form}/submissions', [FormEntryController::class, 'forForm'])->name('form-entries.form');
    Route::get('form-submissions/{formEntry}', [FormEntryController::class, 'show'])->name('form-entries.show');
    Route::get('form-submissions/{formEntry}/edit', [FormEntryController::class, 'edit'])->name('form-entries.edit');
    Route::put('form-submissions/{formEntry}', [FormEntryController::class, 'update'])->name('form-entries.update');
    Route::delete('form-submissions/{formEntry}', [FormEntryController::class, 'destroy'])->name('form-entries.destroy');
    Route::post('form-submissions/{formEntry}/convert-lead', [FormEntryController::class, 'convertToLead'])->name('form-entries.convert-lead');
    Route::post('form-submissions/{formEntry}/convert-customer', [FormEntryController::class, 'convertToCustomer'])->name('form-entries.convert-customer');
});
