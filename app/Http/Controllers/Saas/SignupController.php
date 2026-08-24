<?php

namespace App\Http\Controllers\Saas;

use App\Enums\Platform\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Services\Platform\PlatformActivityLogger;
use App\Services\Platform\StripeBillingService;
use App\Services\Platform\SubscriptionProvisioner;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

    public function store(Request $request, StripeBillingService $billing, TenantProvisioner $provisioner, SubscriptionProvisioner $subscriptions)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64'],
            'plan' => ['required', 'exists:saas_plans,slug'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plan = SaasPlan::where('slug', $data['plan'])->where('is_active', true)->firstOrFail();

        $organization = DB::transaction(function () use ($data, $plan, $provisioner, $subscriptions) {
            $organization = Organization::create([
                'name' => $data['school_name'],
                'slug' => $this->uniqueSlug($data['school_name']),
                'email' => $data['admin_email'],
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
                'contact_name' => $data['admin_name'],
                'status' => OrganizationStatus::Trial,
                'is_active' => true,
            ]);

            $admin = Admin::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'organization_id' => $organization->id,
            ]);

            $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']));

            $subscriptions->createForOrganization(
                $organization,
                $plan,
                $plan->isFree() ? 'complimentary' : 'trial',
            );

            $provisioner->provision($organization);

            return $organization;
        });

        PlatformActivityLogger::log('school.self_signup', "School \"{$organization->name}\" signed up on plan \"{$plan->name}\"", $organization);

        // Hand off to Stripe Checkout when the plan is billable; otherwise the
        // trial starts immediately.
        if (! $plan->isFree() && $plan->isSyncedToStripe() && $billing->isConfigured()) {
            try {
                $url = $billing->createCheckoutSession(
                    $organization,
                    $plan,
                    route('saas.billing.success') . '?session_id={CHECKOUT_SESSION_ID}&org=' . $organization->id,
                    route('saas.billing.cancel') . '?org=' . $organization->id
                );

                return redirect()->away($url);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('saas.signup.success', ['org' => $organization->slug]);
    }

    public function success(Request $request)
    {
        $organization = Organization::where('slug', $request->input('org'))->first();

        return view('saas.signup-success', ['organization' => $organization]);
    }

    public function billingSuccess(Request $request, StripeBillingService $billing)
    {
        // The webhook is the source of truth; this is a fast-path so the user
        // sees their subscription active right after Checkout.
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

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'school';
        $slug = $base;
        $i = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ++$i;
        }

        return $slug;
    }
}
