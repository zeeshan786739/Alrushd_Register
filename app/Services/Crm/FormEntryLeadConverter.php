<?php

namespace App\Services\Crm;

use App\Models\Crm\Lead;
use App\Models\FormEntry;
use App\Support\FormEntryContact;
use Illuminate\Database\UniqueConstraintViolationException;

class FormEntryLeadConverter
{
    /** Integration intakes create CRM leads through their own sync services. */
    private const SKIP_LEGACY_SOURCES = [
        'facebook_lead_ads',
        'tiktok_lead_ads',
    ];

    public function shouldAutoConvert(FormEntry $formEntry): bool
    {
        if ($formEntry->legacy_source && in_array($formEntry->legacy_source, self::SKIP_LEGACY_SOURCES, true)) {
            return false;
        }

        return ($formEntry->organization_id ?? $formEntry->form?->organization_id) !== null;
    }

    public function convertIfMissing(FormEntry $formEntry, ?int $createdBy = null): ?Lead
    {
        $formEntry->loadMissing('form', 'lead');

        if ($formEntry->lead) {
            return $formEntry->lead;
        }

        $existing = Lead::query()
            ->where('form_entry_id', $formEntry->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $organizationId = $formEntry->organization_id ?? $formEntry->form?->organization_id;
        if (! $organizationId) {
            return null;
        }

        $data = FormEntryContact::fromEntry($formEntry);

        try {
            $lead = Lead::create([
                'organization_id' => $organizationId,
                'form_entry_id' => $formEntry->id,
                'source' => 'form_submission',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'company' => $data['company'],
                'lead_source' => $formEntry->form?->name ?? 'Form Submission',
                'lead_status' => 'new',
                'priority' => 'medium',
                'lead_description' => $data['preview'],
                'created_by' => $createdBy,
            ]);
        } catch (UniqueConstraintViolationException) {
            return Lead::query()
                ->where('form_entry_id', $formEntry->id)
                ->first();
        }

        $lead->logActivity(
            'created',
            $createdBy
                ? 'Converted from form submission #'.$formEntry->id
                : 'Auto-added to pipeline from Form Center submission #'.$formEntry->id
        );

        return $lead;
    }

    public function syncPendingForOrganization(int $organizationId, int $limit = 100): int
    {
        $converted = 0;

        FormEntry::query()
            ->with('form')
            ->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                    ->orWhereHas('form', fn ($formQuery) => $formQuery->where('organization_id', $organizationId));
            })
            ->where('status', 'pending')
            ->whereDoesntHave('lead')
            ->where(function ($query) {
                $query->whereNull('legacy_source')
                    ->orWhereNotIn('legacy_source', self::SKIP_LEGACY_SOURCES);
            })
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (FormEntry $entry) use (&$converted) {
                if (! $this->shouldAutoConvert($entry)) {
                    return;
                }

                if ($this->convertIfMissing($entry) instanceof Lead) {
                    $converted++;
                }
            });

        return $converted;
    }
}
