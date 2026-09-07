<?php

namespace App\Support;

use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\FormEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class CrmFormStats
{
    /** @return array<string, int|float> */
    public static function summary(): array
    {
        $entries = FormEntry::forCurrentOrganization();
        $weekStart = Carbon::now()->subDays(7);

        $convertedLeadIds = Lead::forCurrentOrganization()
            ->whereNotNull('form_entry_id')
            ->pluck('form_entry_id');

        return [
            'submissions_total' => (clone $entries)->count(),
            'submissions_pending' => (clone $entries)->where('status', 'pending')->count(),
            'submissions_this_week' => (clone $entries)->where('submitted_at', '>=', $weekStart)->count(),
            'submissions_unconverted' => (clone $entries)
                ->where('status', 'pending')
                ->whereNotIn('id', $convertedLeadIds)
                ->count(),
            'leads_from_forms' => Lead::forCurrentOrganization()->where('source', 'form_submission')->count(),
            'active_forms' => Form::forCurrentOrganization()->where('is_active', true)->count(),
        ];
    }

    /** @return Collection<int, array{id: int, name: string, total: int, pending: int, leads: int, week: int}> */
    public static function formBreakdown(int $limit = 8): Collection
    {
        $weekStart = Carbon::now()->subDays(7);

        return Form::forCurrentOrganization()
            ->where('is_active', true)
            ->orderBy('name')
            ->withCount([
                'entries as submissions_total',
                'entries as submissions_pending' => fn ($q) => $q->where('status', 'pending'),
                'entries as submissions_week' => fn ($q) => $q->where('submitted_at', '>=', $weekStart),
            ])
            ->limit($limit)
            ->get()
            ->map(function (Form $form) {
                $leads = Lead::forCurrentOrganization()
                    ->where('source', 'form_submission')
                    ->whereHas('formEntry', fn ($q) => $q->where('form_id', $form->id))
                    ->count();

                return [
                    'id' => (int) $form->id,
                    'name' => $form->name,
                    'total' => (int) $form->submissions_total,
                    'pending' => (int) $form->submissions_pending,
                    'leads' => $leads,
                    'week' => (int) $form->submissions_week,
                ];
            })
            ->filter(fn (array $row) => $row['total'] > 0 || $row['pending'] > 0)
            ->values();
    }

    /** @return array<int, array{total: int, new: int}> */
    public static function leadCountsByForm(): array
    {
        $rows = Lead::forCurrentOrganization()
            ->whereNotNull('form_entry_id')
            ->whereHas('formEntry')
            ->join('form_entries', 'crm_leads.form_entry_id', '=', 'form_entries.id')
            ->selectRaw('form_entries.form_id as form_id, COUNT(*) as total, SUM(CASE WHEN crm_leads.lead_status = ? THEN 1 ELSE 0 END) as new_count', ['new'])
            ->groupBy('form_entries.form_id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->form_id] = [
                'total' => (int) $row->total,
                'new' => (int) $row->new_count,
            ];
        }

        return $map;
    }
}
