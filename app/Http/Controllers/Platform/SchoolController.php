<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Platform\OrganizationStatus;
use App\Enums\Platform\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DemoRequest;
use App\Models\Organization;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Services\Platform\PlatformActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::query()
            ->withCount('admins')
            ->with('currentSubscription.plan');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planId = $request->input('plan')) {
            $query->whereHas('currentSubscription', fn ($q) => $q->where('saas_plan_id', $planId));
        }

        return view('platform.schools.index', [
            'schools' => $query->latest()->paginate(15)->withQueryString(),
            'plans' => SaasPlan::ordered()->get(),
            'statuses' => OrganizationStatus::cases(),
        ]);
    }

    public function create(Request $request)
    {
        $demoRequest = null;
        if ($request->filled('demo_request_id')) {
            $demoRequest = DemoRequest::find($request->integer('demo_request_id'));
        }

        return view('platform.schools.create', [
            'plans' => SaasPlan::active()->ordered()->get(),
            'statuses' => OrganizationStatus::cases(),
            'demoRequest' => $demoRequest,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:128'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::enum(OrganizationStatus::class)],
            'saas_plan_id' => ['nullable', 'exists:saas_plans,id'],
            'subscription_type' => ['required', Rule::in(['trial', 'complimentary', 'none'])],
            'notes' => ['nullable', 'string'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'demo_request_id' => ['nullable', 'exists:demo_requests,id'],
        ]);

        $status = OrganizationStatus::from($data['status']);
        $plan = ! empty($data['saas_plan_id']) ? SaasPlan::find($data['saas_plan_id']) : null;

        $organization = DB::transaction(function () use ($data, $status, $plan) {
            $organization = Organization::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'contact_name' => $data['contact_name'] ?? null,
                'country' => $data['country'] ?? null,
                'timezone' => $data['timezone'] ?? 'UTC',
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'is_active' => $status->allowsAccess(),
                'trial_ends_at' => $status === OrganizationStatus::Trial
                    ? now()->addDays($plan?->trial_days ?? 14)
                    : null,
                'onboarded_by' => auth('admin')->id(),
            ]);

            $admin = Admin::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'organization_id' => $organization->id,
            ]);

            $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
            $admin->assignRole($role);

            if ($data['subscription_type'] !== 'none') {
                SaasSubscription::create([
                    'organization_id' => $organization->id,
                    'saas_plan_id' => $plan?->id,
                    'status' => $data['subscription_type'] === 'complimentary'
                        ? SubscriptionStatus::Complimentary
                        : SubscriptionStatus::Trialing,
                    'trial_ends_at' => $organization->trial_ends_at,
                ]);
            }

            if (! empty($data['demo_request_id'])) {
                DemoRequest::whereKey($data['demo_request_id'])->update([
                    'status' => 'converted',
                    'converted_organization_id' => $organization->id,
                    'handled_by' => auth('admin')->id(),
                ]);
            }

            return $organization;
        });

        PlatformActivityLogger::log('school.created', "School \"{$organization->name}\" created", $organization, [
            'admin_email' => $data['admin_email'],
            'plan' => $plan?->name,
        ]);

        return redirect()->route('platform.schools.show', $organization)
            ->with('success', 'School created successfully. The school admin can now log in at /admin/login.');
    }

    public function show(Organization $organization)
    {
        $organization->load(['admins' => fn ($q) => $q->latest(), 'subscriptions.plan', 'currentSubscription.plan']);

        $usage = [
            'Leads' => \App\Models\Crm\Lead::where('organization_id', $organization->id)->count(),
            'Customers' => \App\Models\Crm\Customer::where('organization_id', $organization->id)->count(),
            'Forms' => \App\Models\Form::where('organization_id', $organization->id)->count(),
            'Form Entries' => \App\Models\FormEntry::where('organization_id', $organization->id)->count(),
            'Email Campaigns' => \App\Models\EmailMarketing\Campaign::where('organization_id', $organization->id)->count(),
            'Invoices' => \App\Models\Crm\Invoice::where('organization_id', $organization->id)->count(),
        ];

        return view('platform.schools.show', [
            'organization' => $organization,
            'usage' => $usage,
            'activity' => $organization->activityLogs()->with('admin')->take(15)->get(),
            'statuses' => OrganizationStatus::cases(),
        ]);
    }

    public function edit(Organization $organization)
    {
        return view('platform.schools.edit', [
            'organization' => $organization,
            'statuses' => OrganizationStatus::cases(),
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('organizations', 'slug')->ignore($organization->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:128'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);

        $organization->update($data);

        PlatformActivityLogger::log('school.updated', "School \"{$organization->name}\" details updated", $organization);

        return redirect()->route('platform.schools.show', $organization)->with('success', 'School updated.');
    }

    public function updateStatus(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrganizationStatus::class)],
        ]);

        $status = OrganizationStatus::from($data['status']);
        $organization->transitionTo($status);

        PlatformActivityLogger::log('school.status_changed', "School \"{$organization->name}\" set to {$status->label()}", $organization);

        return back()->with('success', "School status changed to {$status->label()}.");
    }

    public function storeAdmin(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'organization_id' => $organization->id,
        ]);

        $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']));

        PlatformActivityLogger::log('school.admin_added', "Admin {$admin->email} added to \"{$organization->name}\"", $organization);

        return back()->with('success', 'School admin account created.');
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
