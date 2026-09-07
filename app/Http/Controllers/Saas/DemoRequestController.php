<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Services\Platform\AccessApprovalService;
use Illuminate\Http\Request;

class DemoRequestController extends Controller
{
    public function create()
    {
        return view('saas.book-demo');
    }

    public function store(Request $request, AccessApprovalService $approvals)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:128'],
            'students_count' => ['nullable', 'string', 'max:64'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $demo = DemoRequest::create($data + ['source' => 'landing']);

        $mailResult = ['applicant' => false, 'owner' => false, 'errors' => ['Mail notify threw an exception']];
        try {
            $mailResult = $approvals->notifyDemoSubmitted($demo);
        } catch (\Throwable $e) {
            report($e);
        }

        $redirect = redirect()->route('saas.demo.create')->with('demo_submitted', true);

        if (! ($mailResult['applicant'] ?? false)) {
            $redirect->with('mail_warning', 'Your request was saved, but the confirmation email could not be sent. Our team still received it and will follow up.');
        }

        return $redirect;
    }
}
