<?php

namespace App\Support;

use App\Enums\IntegrationPlatform;
use App\Enums\Platform\OrganizationStatus;
use App\Models\Admin;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Integrations\MetaLeadSubmission;
use App\Models\Organization;
use App\Support\EmailMarketingDashboard;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class AdminDashboard
{
    /** @return array<string, mixed> */
    public static function data(Admin $admin): array
    {
        $organizationId = OrganizationContext::idOrFail();
        $organization = $admin->organization;
        abort_unless($organization, 404);

        $weekAgo = Carbon::now()->subDays(7);

        return [
            'greeting' => self::greeting($admin),
            'organization' => self::organizationMeta($organization),
            'kpis' => self::kpis($admin, $organizationId, $weekAgo),
            'attention' => self::attention($admin, $organizationId),
            'quickActions' => self::quickActions($admin),
            'modules' => self::modules($admin, $organizationId, $weekAgo),
            'recentSubmissions' => self::recentSubmissions($organizationId),
            'recentLeads' => self::recentLeads($admin, $organizationId),
            'topForms' => self::topForms($organizationId),
            'setupChecklist' => self::setupChecklist($admin, $organizationId),
        ];
    }

    public static function greeting(Admin $admin): string
    {
        $hour = (int) now(config('app.timezone'))->format('G');

        $prefix = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $first = trim(explode(' ', (string) $admin->name)[0] ?? '');

        return $first !== '' ? "{$prefix}, {$first}" : $prefix;
    }

    /** @return array<string, mixed> */
    public static function organizationMeta(Organization $organization): array
    {
        $subscription = $organization->currentSubscription()->with('plan')->first();
        $status = $organization->status instanceof OrganizationStatus
            ? $organization->status
            : OrganizationStatus::tryFrom((string) $organization->status);

        $trialDaysLeft = null;
        if ($status === OrganizationStatus::Trial && $organization->trial_ends_at) {
            $trialDaysLeft = max(0, (int) now()->diffInDays($organization->trial_ends_at, false));
        }

        return [
            'name' => $organization->name,
            'status' => $status?->value,
            'status_label' => $status?->label() ?? 'Active',
            'status_class' => $status?->badgeClass() ?? 'bg-success-focus text-success-main',
            'trial_days_left' => $trialDaysLeft,
            'plan_name' => $subscription?->plan?->name,
            'subscription_status' => $subscription?->status,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(Admin $admin, int $organizationId, Carbon $weekAgo): array
    {
        $items = [];

        if ($admin->can('view leads')) {
            $leads = Lead::forCurrentOrganization();
            $total = (clone $leads)->count();
            $newThisWeek = (clone $leads)->where('created_at', '>=', $weekAgo)->count();
            $followUpToday = (clone $leads)->followUpToday()->count();

            $items[] = [
                'label' => 'CRM leads',
                'value' => number_format($total),
                'meta' => $newThisWeek > 0
                    ? '+'.$newThisWeek.' this week'
                    : ($followUpToday > 0 ? $followUpToday.' follow-up today' : 'Pipeline overview'),
                'icon' => 'solar:user-hand-up-linear',
                'tone' => 'navy',
                'href' => route('admin.crm.leads.index'),
            ];
        }

        if ($admin->can('view invoices')) {
            $outstanding = (float) Invoice::forCurrentOrganization()
                ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
                ->sum('due_amount');
            $paidThisWeek = (float) Invoice::forCurrentOrganization()
                ->where('updated_at', '>=', $weekAgo)
                ->sum('paid_amount');

            $items[] = [
                'label' => 'Outstanding',
                'value' => number_format($outstanding, 2),
                'prefix' => '$',
                'meta' => $paidThisWeek > 0
                    ? '$'.number_format($paidThisWeek, 2).' collected this week'
                    : 'Open invoice balance',
                'icon' => 'solar:bill-list-linear',
                'tone' => 'gold',
                'href' => route('admin.crm.invoices.index'),
            ];
        }

        $formStats = self::formStats($organizationId, $weekAgo);
        $items[] = [
            'label' => 'Form submissions',
            'value' => number_format($formStats['total_submissions']),
            'meta' => $formStats['submissions_this_week'] > 0
                ? '+'.$formStats['submissions_this_week'].' this week'
                : $formStats['active_forms'].' live forms',
            'icon' => 'solar:document-add-linear',
            'tone' => 'purple',
            'href' => route('admin.form-manager.index'),
        ];

        if ($admin->canany(['view inbox', 'view campaigns', 'view templates'])) {
            $email = EmailMarketingDashboard::stats($organizationId);
            $items[] = [
                'label' => 'Marketing inbox',
                'value' => number_format($email['inbox_unread']),
                'meta' => $email['campaigns_total'].' campaigns · '.$email['templates_total'].' templates',
                'icon' => 'solar:letter-linear',
                'tone' => 'cyan',
                'href' => route('admin.email.dashboard'),
            ];
        }

        if ($admin->can('view integrations')) {
            $facebookConnection = IntegrationConnection::query()
                ->where('organization_id', $organizationId)
                ->where('platform', IntegrationPlatform::Facebook)
                ->first();
            $facebookConnected = $facebookConnection?->isConnected() ?? false;
            $integrationLeads = MetaLeadSubmission::query()
                ->where('organization_id', $organizationId)
                ->count();

            $items[] = [
                'label' => 'Integration leads',
                'value' => number_format($integrationLeads),
                'meta' => $facebookConnected ? 'Facebook connected' : 'Connect Facebook or TikTok',
                'icon' => 'solar:plug-circle-linear',
                'tone' => 'amber',
                'href' => route('admin.integrations.hub'),
            ];
        }

        if ($admin->can('view user')) {
            $team = UserManagementHelper::stats();
            $items[] = [
                'label' => 'Team members',
                'value' => number_format($team['users']),
                'meta' => $team['roles'].' roles configured',
                'icon' => 'solar:users-group-two-rounded-linear',
                'tone' => 'green',
                'href' => route('admin.user-management.index'),
            ];
        }

        return array_slice($items, 0, 6);
    }

    /** @return array<string, int> */
    public static function formStats(int $organizationId, Carbon $weekAgo): array
    {
        $forms = Form::query()
            ->where('organization_id', $organizationId)
            ->withCount('entries')
            ->get();

        return [
            'total_forms' => $forms->count(),
            'active_forms' => $forms->where('is_active', true)->count(),
            'landing_forms' => $forms->filter(fn (Form $form) => $form->hasPlacement('landing'))->count(),
            'total_submissions' => (int) $forms->sum('entries_count'),
            'submissions_this_week' => FormEntry::forCurrentOrganization()
                ->where('submitted_at', '>=', $weekAgo)
                ->count(),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function attention(Admin $admin, int $organizationId): Collection
    {
        $items = collect();

        if ($admin->canany(['view inbox', 'view campaigns', 'manage mailbox settings'])) {
            foreach (EmailMarketingDashboard::attention($organizationId) as $item) {
                $items->push([
                    'severity' => $item['severity'] ?? 'neutral',
                    'type' => 'Email',
                    'label' => $item['label'],
                    'meta' => $item['meta'] ?? '',
                    'url' => $item['url'],
                ]);
            }
        }

        if ($admin->can('view leads')) {
            $overdue = Lead::forCurrentOrganization()->overdueFollowUp()->count();
            if ($overdue > 0) {
                $items->push([
                    'severity' => 'danger',
                    'type' => 'CRM',
                    'label' => $overdue.' lead follow-up'.($overdue === 1 ? '' : 's').' overdue',
                    'meta' => 'Review and contact today',
                    'url' => route('admin.crm.leads.index'),
                ]);
            }

            $today = Lead::forCurrentOrganization()->followUpToday()->count();
            if ($today > 0) {
                $items->push([
                    'severity' => 'warning',
                    'type' => 'CRM',
                    'label' => $today.' follow-up'.($today === 1 ? '' : 's').' due today',
                    'meta' => 'Leads waiting for contact',
                    'url' => route('admin.crm.leads.index'),
                ]);
            }
        }

        if ($admin->can('view invoices')) {
            $overdueCount = Invoice::forCurrentOrganization()
                ->where('due_amount', '>', 0)
                ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
                ->whereDate('due_date', '<', now()->toDateString())
                ->count();

            if ($overdueCount > 0) {
                $items->push([
                    'severity' => 'danger',
                    'type' => 'Billing',
                    'label' => $overdueCount.' overdue invoice'.($overdueCount === 1 ? '' : 's'),
                    'meta' => 'Collect outstanding payments',
                    'url' => route('admin.crm.invoices.index'),
                ]);
            }
        }

        if ($admin->can('view integrations')) {
            $unmapped = MetaLeadSubmission::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'unmapped')
                ->count();

            if ($unmapped > 0) {
                $items->push([
                    'severity' => 'warning',
                    'type' => 'Integrations',
                    'label' => $unmapped.' unmapped Facebook lead'.($unmapped === 1 ? '' : 's'),
                    'meta' => 'Map fields to CRM leads',
                    'url' => route('admin.integrations.hub'),
                ]);
            }
        }

        return $items
            ->sortBy(fn (array $item) => match ($item['severity']) {
                'danger' => 0,
                'warning' => 1,
                'info' => 2,
                default => 3,
            })
            ->values()
            ->take(8);
    }

    /** @return array<int, array<string, mixed>> */
    public static function quickActions(Admin $admin): array
    {
        return array_values(array_filter([
            $admin->canany(['view leads', 'view customers', 'view projects', 'view quotations', 'view invoices']) ? [
                'label' => 'CRM overview',
                'desc' => 'Pipeline & revenue',
                'url' => route('admin.crm.overview'),
                'icon' => 'solar:chart-2-linear',
            ] : null,
            $admin->can('create campaigns') ? [
                'label' => 'New campaign',
                'desc' => 'Email your audience',
                'url' => route('admin.email.campaigns.create'),
                'icon' => 'solar:megaphone-linear',
            ] : null,
            [
                'label' => 'Form Center',
                'desc' => 'Build & manage forms',
                'url' => route('admin.form-manager.index'),
                'icon' => 'solar:document-add-linear',
            ],
            $admin->can('create user') ? [
                'label' => 'Invite teammate',
                'desc' => 'Add admin access',
                'url' => route('admin.users.create'),
                'icon' => 'solar:user-plus-linear',
            ] : null,
            $admin->can('compose emails') ? [
                'label' => 'Compose email',
                'desc' => 'One-to-one message',
                'url' => route('admin.email.compose'),
                'icon' => 'solar:pen-new-square-linear',
            ] : null,
            $admin->can('view integrations') ? [
                'label' => 'Integrations',
                'desc' => 'Facebook & TikTok',
                'url' => route('admin.integrations.hub'),
                'icon' => 'solar:plug-circle-linear',
            ] : null,
            $admin->can('view dashboard') ? [
                'label' => 'Website CMS',
                'desc' => 'Edit your site',
                'url' => route('admin.settings.index'),
                'icon' => 'solar:monitor-smartphone-linear',
            ] : null,
        ]));
    }

    /** @return array<int, array<string, mixed>> */
    public static function modules(Admin $admin, int $organizationId, Carbon $weekAgo): array
    {
        $modules = [];

        if ($admin->canany(['view leads', 'view customers', 'view projects', 'view quotations', 'view invoices'])) {
            $leads = Lead::forCurrentOrganization();
            $invoices = Invoice::forCurrentOrganization()->where('status', '!=', 'cancelled');

            $modules[] = [
                'title' => 'CRM',
                'desc' => 'Leads, quotes, invoices & projects',
                'url' => route('admin.crm.overview'),
                'icon' => 'solar:chart-2-linear',
                'stats' => array_values(array_filter([
                    $admin->can('view leads') ? ['label' => 'Leads', 'value' => number_format((clone $leads)->count())] : null,
                    $admin->can('view invoices') ? ['label' => 'Invoiced', 'value' => '$'.number_format((float) (clone $invoices)->sum('total'), 0)] : null,
                    $admin->can('view leads') ? ['label' => 'New (7d)', 'value' => number_format((clone $leads)->where('created_at', '>=', $weekAgo)->count())] : null,
                ])),
            ];
        }

        $formStats = self::formStats($organizationId, $weekAgo);
        $modules[] = [
            'title' => 'Forms',
            'desc' => 'Admissions, enquiries & custom forms',
            'url' => route('admin.form-manager.index'),
            'icon' => 'solar:clipboard-text-linear',
            'stats' => [
                ['label' => 'Live', 'value' => number_format($formStats['active_forms'])],
                ['label' => 'Submissions', 'value' => number_format($formStats['total_submissions'])],
                ['label' => 'This week', 'value' => number_format($formStats['submissions_this_week'])],
            ],
        ];

        if ($admin->canany(['view inbox', 'view campaigns', 'view templates'])) {
            $email = EmailMarketingDashboard::stats($organizationId);
            $modules[] = [
                'title' => 'Email Marketing',
                'desc' => 'Campaigns, inbox & templates',
                'url' => route('admin.email.dashboard'),
                'icon' => 'solar:letter-linear',
                'stats' => [
                    ['label' => 'Unread', 'value' => number_format($email['inbox_unread'])],
                    ['label' => 'Campaigns', 'value' => number_format($email['campaigns_total'])],
                    ['label' => 'Reach', 'value' => number_format($email['audience_leads'] + $email['audience_customers'])],
                ],
            ];
        }

        if ($admin->can('view user')) {
            $team = UserManagementHelper::stats();
            $modules[] = [
                'title' => 'Team & Access',
                'desc' => 'Roles, permissions & admins',
                'url' => route('admin.user-management.index'),
                'icon' => 'solar:users-group-two-rounded-linear',
                'stats' => [
                    ['label' => 'Members', 'value' => number_format($team['users'])],
                    ['label' => 'Roles', 'value' => number_format($team['roles'])],
                    ['label' => 'Permissions', 'value' => number_format($team['permissions'])],
                ],
            ];
        }

        return $modules;
    }

    /** @return Collection<int, FormEntry> */
    public static function recentSubmissions(int $organizationId): Collection
    {
        return FormEntry::forCurrentOrganization()
            ->with(['form:id,name,slug,organization_id'])
            ->latest('submitted_at')
            ->limit(6)
            ->get();
    }

    /** @return Collection<int, Lead> */
    public static function recentLeads(Admin $admin, int $organizationId): Collection
    {
        if (! $admin->can('view leads')) {
            return collect();
        }

        return Lead::forCurrentOrganization()
            ->latest()
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'email', 'lead_status', 'source', 'created_at']);
    }

    /** @return Collection<int, Form> */
    public static function topForms(int $organizationId): Collection
    {
        return Form::query()
            ->where('organization_id', $organizationId)
            ->withCount('entries')
            ->orderByDesc('entries_count')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /** @return array<int, array<string, mixed>> */
    public static function setupChecklist(Admin $admin, int $organizationId): array
    {
        $items = [];
        $formStats = self::formStats($organizationId, Carbon::now()->subDays(7));
        $team = UserManagementHelper::stats();
        $email = EmailMarketingDashboard::stats($organizationId);

        if ($admin->canany(['manage mailbox settings', 'view campaigns']) && ! $email['mailbox_connected']) {
            $items[] = [
                'label' => 'Connect your mailbox',
                'desc' => 'Send campaigns and receive replies',
                'url' => route('admin.email.mailbox.settings'),
                'done' => false,
                'icon' => 'solar:letter-linear',
            ];
        }

        if ($admin->can('view integrations')) {
            $facebookConnection = IntegrationConnection::query()
                ->where('organization_id', $organizationId)
                ->where('platform', IntegrationPlatform::Facebook)
                ->first();
            $facebookConnected = $facebookConnection?->isConnected() ?? false;

            $items[] = [
                'label' => 'Connect Facebook Lead Ads',
                'desc' => 'Import leads automatically',
                'url' => route('admin.integrations.hub'),
                'done' => $facebookConnected,
                'icon' => 'logos:facebook',
            ];
        }

        if ($admin->can('create user') && $team['users'] <= 1) {
            $items[] = [
                'label' => 'Invite your team',
                'desc' => 'Give staff their own login',
                'url' => route('admin.users.create'),
                'done' => $team['users'] > 1,
                'icon' => 'solar:user-plus-linear',
            ];
        }

        if ($formStats['total_forms'] === 0) {
            $items[] = [
                'label' => 'Create your first form',
                'desc' => 'Capture admissions & enquiries',
                'url' => route('admin.form-manager.create'),
                'done' => false,
                'icon' => 'solar:document-add-linear',
            ];
        }

        if ($admin->can('view dashboard')) {
            $items[] = [
                'label' => 'Customize your website',
                'desc' => 'Homepage, branding & content',
                'url' => route('admin.settings.index'),
                'done' => false,
                'icon' => 'solar:monitor-smartphone-linear',
            ];
        }

        return $items;
    }
}
