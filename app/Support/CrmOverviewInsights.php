<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class CrmOverviewInsights
{
    /**
     * @param  array<string, mixed>  $stats
     * @param  Collection<int, array<string, mixed>>  $attention
     * @return array{
     *     brief: string,
     *     tone: string,
     *     actions: list<array{label: string, url: string, tone: string, icon: string}>,
     *     signals: list<array{label: string, value: string, href: ?string, accent: string}>
     * }
     */
    public static function generate(array $stats, Collection $attention, ?\App\Models\Admin $admin = null): array
    {
        $actions = [];
        $signals = [];
        $briefParts = [];

        $attentionCount = $attention->count();
        $leadsNew = (int) ($stats['leads_new'] ?? 0);
        $leadsOverdue = (int) ($stats['leads_follow_up_overdue'] ?? 0);
        $leadsToday = (int) ($stats['leads_follow_up_today'] ?? 0);
        $pendingForms = (int) ($stats['submissions_pending'] ?? 0);
        $outstanding = (float) ($stats['outstanding'] ?? 0);
        $overdueInvoices = (float) ($stats['overdue_invoices'] ?? 0);
        $projectsOverdue = (int) ($stats['projects_overdue'] ?? 0);
        $projectsDueSoon = (int) ($stats['projects_due_soon'] ?? 0);

        if ($attentionCount === 0) {
            $briefParts[] = 'Your workspace is clear — no urgent items are flagged right now.';
        } else {
            $briefParts[] = sprintf(
                '%d %s need%s your attention today.',
                $attentionCount,
                $attentionCount === 1 ? 'item' : 'items',
                $attentionCount === 1 ? 's' : ''
            );
        }

        if ($leadsOverdue > 0 && ($admin === null || $admin->can('view leads'))) {
            $briefParts[] = sprintf('%s overdue follow-up%s should be handled first.', number_format($leadsOverdue), $leadsOverdue === 1 ? '' : 's');
            $actions[] = [
                'label' => 'Overdue follow-ups',
                'url' => route('admin.crm.leads.index', ['follow_up' => 'overdue', 'view' => 'list']),
                'tone' => 'danger',
                'icon' => 'solar:alarm-linear',
            ];
        } elseif ($leadsToday > 0 && ($admin === null || $admin->can('view leads'))) {
            $briefParts[] = sprintf('%s follow-up%s due today.', number_format($leadsToday), $leadsToday === 1 ? '' : 's');
            $actions[] = [
                'label' => 'Due today',
                'url' => route('admin.crm.leads.index', ['follow_up' => 'today', 'view' => 'list']),
                'tone' => 'warning',
                'icon' => 'solar:calendar-linear',
            ];
        }

        if ($pendingForms > 0 && ($admin === null || $admin->can('view form submissions'))) {
            $briefParts[] = sprintf('%s form submission%s waiting for review.', number_format($pendingForms), $pendingForms === 1 ? '' : 's');
            $actions[] = [
                'label' => 'Review forms',
                'url' => route('admin.crm.form-entries.index', ['status' => 'pending']),
                'tone' => 'amber',
                'icon' => 'solar:inbox-in-linear',
            ];
        }

        if ($leadsNew > 0 && $leadsNew >= max(10, (int) ($stats['leads_total'] ?? 0) * 0.5) && ($admin === null || $admin->can('view leads'))) {
            $briefParts[] = sprintf('%s new leads make up most of the pipeline — consider assigning owners.', number_format($leadsNew));
            $actions[] = [
                'label' => 'Triage new leads',
                'url' => route('admin.crm.leads.index', ['lead_status' => 'new']),
                'tone' => 'brand',
                'icon' => 'solar:star-linear',
            ];
        }

        if ($overdueInvoices > 0 && ($admin === null || $admin->can('view invoices'))) {
            $actions[] = [
                'label' => 'Overdue invoices',
                'url' => route('admin.crm.invoices.index'),
                'tone' => 'danger',
                'icon' => 'solar:bill-list-linear',
            ];
        }

        if ($projectsOverdue > 0 && ($admin === null || $admin->can('view projects'))) {
            $actions[] = [
                'label' => 'Overdue projects',
                'url' => route('admin.crm.projects.index'),
                'tone' => 'danger',
                'icon' => 'solar:folder-linear',
            ];
        } elseif ($projectsDueSoon > 0 && ($admin === null || $admin->can('view projects'))) {
            $actions[] = [
                'label' => 'Projects due soon',
                'url' => route('admin.crm.projects.index'),
                'tone' => 'warning',
                'icon' => 'solar:calendar-mark-linear',
            ];
        }

        if (empty($actions) && $attentionCount === 0 && ($admin === null || $admin->can('view leads'))) {
            $actions[] = [
                'label' => 'Open leads board',
                'url' => route('admin.crm.leads.index'),
                'tone' => 'brand',
                'icon' => 'solar:kanban-linear',
            ];
        }

        $actions = collect($actions)->unique('url')->take(4)->values()->all();

        if (isset($stats['leads_total']) && ($admin === null || $admin->can('view leads'))) {
            $signals[] = [
                'label' => 'Leads',
                'value' => number_format((int) $stats['leads_total']),
                'href' => route('admin.crm.leads.index'),
                'accent' => 'brand',
            ];
        }
        if ($leadsNew > 0) {
            $signals[] = [
                'label' => 'New',
                'value' => number_format($leadsNew),
                'href' => route('admin.crm.leads.index', ['lead_status' => 'new']),
                'accent' => 'purple',
            ];
        }
        if (($outstanding > 0 || isset($stats['outstanding'])) && ($admin === null || $admin->can('view invoices'))) {
            $signals[] = [
                'label' => 'Outstanding',
                'value' => number_format($outstanding, 2),
                'href' => route('admin.crm.invoices.index'),
                'accent' => $overdueInvoices > 0 ? 'red' : 'gold',
            ];
        }
        if ($attentionCount > 0) {
            $signals[] = [
                'label' => 'Attention',
                'value' => (string) $attentionCount,
                'href' => '#crm-overview-attention',
                'accent' => 'red',
            ];
        }

        $tone = $attentionCount > 0 || $leadsOverdue > 0 || $overdueInvoices > 0 ? 'focus' : 'calm';

        return [
            'brief' => implode(' ', $briefParts),
            'tone' => $tone,
            'actions' => $actions,
            'signals' => array_slice($signals, 0, 4),
        ];
    }
}
