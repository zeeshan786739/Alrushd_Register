<?php

namespace App\Services\Platform;

use App\Enums\Platform\DemoRequestStatus;
use App\Enums\Platform\OrganizationStatus;
use App\Enums\Platform\TrialSignupStatus;
use App\Models\Admin;
use App\Models\DemoRequest;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Models\TrialSignupRequest;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Owner-approval workflows for demo access and free-trial signups.
 */
class AccessApprovalService
{
    public function __construct(
        private PlatformMailService $mail,
        private SubscriptionProvisioner $subscriptions,
        private TenantProvisioner $tenantProvisioner,
    ) {
    }

    /**
     * @return array{applicant: bool, owner: bool, errors: list<string>}
     */
    public function notifyDemoSubmitted(DemoRequest $demo): array
    {
        $errors = [];

        $applicant = $this->mail->sendView(
            $demo->email,
            'We received your demo request — '.$this->mail->platformName(),
            'emails.platform.demo-confirmation',
            ['demo' => $demo]
        );
        if (! $applicant->accepted) {
            $errors[] = 'Applicant confirmation: '.($applicant->error ?: 'failed');
        }

        $owner = $this->mail->sendView(
            $this->mail->notifyEmail(),
            'New demo request: '.$demo->organization_name,
            'emails.platform.demo-owner-notification',
            [
                'demo' => $demo,
                'reviewUrl' => route('platform.demo-requests.show', $demo),
            ]
        );
        if (! $owner->accepted) {
            $errors[] = 'Owner notification: '.($owner->error ?: 'failed');
        }

        return [
            'applicant' => $applicant->accepted,
            'owner' => $owner->accepted,
            'errors' => $errors,
        ];
    }

    /**
     * Grant demo access: provision a trial workspace and email a set-password link.
     *
     * @return array{organization: Organization, setPasswordUrl: string}
     */
    public function approveDemo(DemoRequest $demo, ?SaasPlan $plan = null): array
    {
        if ($demo->converted_organization_id && $demo->access_granted_at) {
            throw new \RuntimeException('Demo access has already been granted for this request.');
        }

        $plan ??= SaasPlan::defaultPlan() ?? SaasPlan::active()->ordered()->first();
        if (! $plan) {
            throw new \RuntimeException('No active plan is available to provision demo access.');
        }

        if (Admin::where('email', $demo->email)->exists()) {
            throw new \RuntimeException('An admin account with this email already exists.');
        }

        [$organization, $admin] = DB::transaction(function () use ($demo, $plan) {
            $organization = Organization::create([
                'name' => $demo->organization_name ?: ($demo->name.' School'),
                'slug' => $this->uniqueSlug($demo->organization_name ?: $demo->name),
                'email' => $demo->email,
                'phone' => $demo->phone,
                'country' => $demo->country,
                'contact_name' => $demo->name,
                'status' => OrganizationStatus::Trial,
                'is_active' => true,
                'onboarded_by' => auth('admin')->id(),
                'notes' => 'Provisioned from demo request #'.$demo->id,
            ]);

            // Placeholder password — applicant sets their own via the emailed link.
            $admin = Admin::create([
                'name' => $demo->name,
                'email' => $demo->email,
                'password' => Hash::make(Str::password(32)),
                'organization_id' => $organization->id,
            ]);

            $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']));

            $this->subscriptions->createForOrganization(
                $organization,
                $plan,
                $plan->isFree() ? 'complimentary' : 'trial',
            );

            $this->tenantProvisioner->provision($organization);

            $demo->update([
                'status' => DemoRequestStatus::Approved,
                'converted_organization_id' => $organization->id,
                'handled_by' => auth('admin')->id(),
                'access_granted_at' => now(),
            ]);

            return [$organization, $admin];
        });

        $setPasswordUrl = $this->createSetPasswordUrl($admin);

        try {
            $this->mail->sendView(
                $demo->email,
                'Your demo access is ready — '.$this->mail->platformName(),
                'emails.platform.demo-access-granted',
                [
                    'demo' => $demo->fresh(),
                    'organization' => $organization,
                    'setPasswordUrl' => $setPasswordUrl,
                    'loginUrl' => route('admin.login'),
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        PlatformActivityLogger::log(
            'demo_request.approved',
            "Demo access granted for {$demo->email} → school \"{$organization->name}\"",
            $organization
        );

        return ['organization' => $organization, 'setPasswordUrl' => $setPasswordUrl];
    }

    /**
     * @return array{applicant: bool, owner: bool, errors: list<string>}
     */
    public function notifyTrialSubmitted(TrialSignupRequest $request): array
    {
        $errors = [];

        $applicant = $this->mail->sendView(
            $request->admin_email,
            'We received your free trial request — '.$this->mail->platformName(),
            'emails.platform.trial-confirmation',
            ['request' => $request->loadMissing('plan')]
        );
        if (! $applicant->accepted) {
            $errors[] = 'Applicant confirmation: '.($applicant->error ?: 'failed');
        }

        $owner = $this->mail->sendView(
            $this->mail->notifyEmail(),
            'New free trial request: '.$request->school_name,
            'emails.platform.trial-owner-notification',
            [
                'request' => $request->loadMissing('plan'),
                'reviewUrl' => route('platform.trial-requests.show', $request),
            ]
        );
        if (! $owner->accepted) {
            $errors[] = 'Owner notification: '.($owner->error ?: 'failed');
        }

        return [
            'applicant' => $applicant->accepted,
            'owner' => $owner->accepted,
            'errors' => $errors,
        ];
    }

    public function approveTrial(TrialSignupRequest $request): Organization
    {
        if (! $request->isPending()) {
            throw new \RuntimeException('This trial request has already been processed.');
        }

        $plan = $request->plan ?? SaasPlan::find($request->saas_plan_id);
        if (! $plan || ! $plan->is_active) {
            throw new \RuntimeException('The selected plan is no longer available.');
        }

        if (Admin::where('email', $request->admin_email)->exists()) {
            throw new \RuntimeException('An admin account with this email already exists.');
        }

        [$organization, $admin] = DB::transaction(function () use ($request, $plan) {
            $organization = Organization::create([
                'name' => $request->school_name,
                'slug' => $this->uniqueSlug($request->school_name),
                'email' => $request->admin_email,
                'phone' => $request->phone,
                'country' => $request->country,
                'contact_name' => $request->admin_name,
                'status' => OrganizationStatus::Trial,
                'is_active' => true,
                'onboarded_by' => auth('admin')->id(),
            ]);

            // Prefer the password they chose at signup; they can still change it via the set-password link.
            $admin = Admin::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => $request->password_hash ?: Hash::make(Str::password(32)),
                'organization_id' => $organization->id,
            ]);

            $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']));

            $this->subscriptions->createForOrganization(
                $organization,
                $plan,
                $plan->isFree() ? 'complimentary' : 'trial',
            );

            $this->tenantProvisioner->provision($organization);

            $request->update([
                'status' => TrialSignupStatus::Approved,
                'organization_id' => $organization->id,
                'handled_by' => auth('admin')->id(),
                'approved_at' => now(),
            ]);

            return [$organization, $admin];
        });

        $setPasswordUrl = $this->createSetPasswordUrl($admin);

        try {
            $this->mail->sendView(
                $request->admin_email,
                'Your free trial is ready — '.$this->mail->platformName(),
                'emails.platform.trial-access-granted',
                [
                    'request' => $request->fresh(['plan']),
                    'organization' => $organization,
                    'setPasswordUrl' => $setPasswordUrl,
                    'loginUrl' => route('admin.login'),
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        PlatformActivityLogger::log(
            'trial_request.approved',
            "Free trial approved for {$request->admin_email} → school \"{$organization->name}\"",
            $organization,
            ['plan' => $plan->name]
        );

        return $organization;
    }

    public function rejectTrial(TrialSignupRequest $request, ?string $reason = null): void
    {
        if (! $request->isPending()) {
            throw new \RuntimeException('This trial request has already been processed.');
        }

        $request->update([
            'status' => TrialSignupStatus::Rejected,
            'handled_by' => auth('admin')->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        try {
            $this->mail->sendView(
                $request->admin_email,
                'Update on your free trial request — '.$this->mail->platformName(),
                'emails.platform.trial-rejected',
                [
                    'request' => $request->fresh(['plan']),
                    'reason' => $reason,
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        PlatformActivityLogger::log(
            'trial_request.rejected',
            "Free trial rejected for {$request->admin_email}",
            null,
            ['reason' => $reason]
        );
    }

    /**
     * When converting a demo via "Create School", email a set-password link if access was not emailed yet.
     */
    public function notifyDemoConverted(DemoRequest $demo, Organization $organization, ?string $plainPassword = null): void
    {
        if ($demo->access_granted_at) {
            return;
        }

        $admin = Admin::query()
            ->where('organization_id', $organization->id)
            ->where('email', $demo->email)
            ->first()
            ?? Admin::query()->where('organization_id', $organization->id)->latest('id')->first();

        $setPasswordUrl = $admin ? $this->createSetPasswordUrl($admin) : route('admin.password.request');

        $this->mail->sendView(
            $demo->email,
            'Your demo access is ready — '.$this->mail->platformName(),
            'emails.platform.demo-access-granted',
            [
                'demo' => $demo,
                'organization' => $organization,
                'setPasswordUrl' => $setPasswordUrl,
                'loginUrl' => route('admin.login'),
            ]
        );

        $demo->update(['access_granted_at' => now()]);
    }

    private function createSetPasswordUrl(Admin $admin): string
    {
        $token = Password::broker('admins')->createToken($admin);

        return route('admin.invitation.accept', [
            'token' => $token,
            'email' => $admin->email,
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'school';
        $slug = $base;
        $i = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
