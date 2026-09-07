<?php

namespace App\Http\Controllers\Saas;

use App\Enums\Platform\TrialSignupStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Models\TrialSignupRequest;
use App\Services\Platform\AccessApprovalService;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SignupController extends Controller
{
    public function create(Request $request)
    {
        $plans = SaasPlan::active()->ordered()->get();

        $selected = $request->filled('plan')
            ? $plans->firstWhere('slug', $request->input('plan'))
            : (SaasPlan::defaultPlan() ?? $plans->firstWhere('is_featured', true) ?? $plans->first());

        return view('saas.signup', [
            'plans' => $plans,
            'selectedPlan' => $selected,
        ]);
    }

    public function store(Request $request, AccessApprovalService $approvals)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64'],
            'plan' => ['required', 'exists:saas_plans,slug'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => [
                'required',
                'email',
                'max:255',
                'unique:admins,email',
                Rule::unique('trial_signup_requests', 'admin_email')
                    ->where(fn ($q) => $q->where('status', TrialSignupStatus::Pending->value)),
            ],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'admin_email.unique' => 'This email already has an account or a pending free trial request.',
        ]);

        $plan = SaasPlan::where('slug', $data['plan'])->where('is_active', true)->firstOrFail();

        $trialRequest = TrialSignupRequest::create([
            'school_name' => $data['school_name'],
            'country' => $data['country'] ?? null,
            'phone' => $data['phone'] ?? null,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'password_hash' => Hash::make($data['admin_password']),
            'saas_plan_id' => $plan->id,
            'status' => TrialSignupStatus::Pending,
            'source' => 'landing',
        ]);

        PlatformActivityLogger::log(
            'trial_request.submitted',
            "Free trial requested by {$trialRequest->admin_email} for \"{$trialRequest->school_name}\" ({$plan->name})"
        );

        $mailResult = ['applicant' => false, 'owner' => false, 'errors' => []];
        try {
            $mailResult = $approvals->notifyTrialSubmitted($trialRequest);
        } catch (\Throwable $e) {
            report($e);
        }

        $redirect = redirect()->route('saas.signup.success', [
            'pending' => 1,
            'email' => $trialRequest->admin_email,
        ]);

        if (! ($mailResult['applicant'] ?? false)) {
            $redirect->with('mail_warning', 'Your request was saved, but the confirmation email could not be sent. Our team will still review it.');
        }

        return $redirect;
    }

    public function success(Request $request)
    {
        if ($request->boolean('pending')) {
            return view('saas.signup-success', [
                'pendingApproval' => true,
                'applicantEmail' => $request->input('email'),
                'organization' => null,
            ]);
        }

        $organization = Organization::where('slug', $request->input('org'))->first();

        return view('saas.signup-success', ['organization' => $organization]);
    }

    public function billingSuccess(Request $request, StripeBillingService $billing)
    {
        if ($request->filled('session_id') && $billing->isConfigured()) {
            try {
                $session = $billing->client()->checkout->sessions->retrieve($request->input('session_id'));
                if ($session->subscription) {
                    $billing->handleEvent(new \Stripe\Event([
                        'type' => 'checkout.session.completed',
                        'data' => ['object' => $session],
                    ]));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $organization = Organization::find($request->input('org'));

        return view('saas.signup-success', [
            'organization' => $organization,
            'paid' => true,
        ]);
    }

    public function billingCancel(Request $request)
    {
        $organization = Organization::find($request->input('org'));

        return view('saas.signup-success', [
            'organization' => $organization,
            'checkoutCancelled' => true,
        ]);
    }
}
