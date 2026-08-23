<?php

namespace App\Services\EmailMarketing;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\FormEntry;
use Illuminate\Support\Collection;

class CampaignRecipientResolver
{
    public function __construct(private SuppressionService $suppressions)
    {
    }

    /**
     * @param  array{source:string, lead_ids?:array<int>, customer_ids?:array<int>, form_entry_ids?:array<int>, manual_emails?:string, lead_status?:?string, lead_source?:?string, form_id?:?int}  $options
     * @return Collection<int, array{email:string,name:?string,lead_id:?int,customer_id:?int,form_entry_id:?int}>
     */
    public function rawCandidates(int $organizationId, array $options): Collection
    {
        $source = $options['source'] ?? 'manual';

        return match ($source) {
            'leads' => $this->fromLeads($organizationId, $options),
            'customers' => $this->fromCustomers($organizationId, $options),
            'form_entries' => $this->fromFormEntries($organizationId, $options),
            'integration_leads' => $this->fromLeads($organizationId, $options, [], true),
            'selected_leads' => $this->fromLeads($organizationId, $options, $options['lead_ids'] ?? []),
            'selected_customers' => $this->fromCustomers($organizationId, $options, $options['customer_ids'] ?? []),
            'selected_form_entries' => $this->fromFormEntries($organizationId, $options, $options['form_entry_ids'] ?? []),
            default => $this->fromManual($options['manual_emails'] ?? ''),
        };
    }

    /**
     * @param  array{source:string, lead_ids?:array<int>, customer_ids?:array<int>, form_entry_ids?:array<int>, manual_emails?:string, lead_status?:?string, lead_source?:?string, form_id?:?int}  $options
     * @return Collection<int, array{email:string,name:?string,lead_id:?int,customer_id:?int,form_entry_id:?int}>
     */
    public function resolve(int $organizationId, array $options): Collection
    {
        $rows = $this->rawCandidates($organizationId, $options);

        return $rows
            ->filter(fn ($row) => filter_var($row['email'], FILTER_VALIDATE_EMAIL))
            ->reject(fn ($row) => $this->suppressions->isMarketingBlocked($organizationId, $row['email']))
            ->unique(fn ($row) => $this->suppressions->normalize($row['email']))
            ->values();
    }

    /** @param  array<int, int>  $ids */
    private function fromLeads(int $organizationId, array $options, array $ids = [], bool $integrationOnly = false): Collection
    {
        $query = Lead::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        if ($integrationOnly && ! empty($options['lead_source'])) {
            $query->where('source', $options['lead_source']);
        } elseif ($integrationOnly) {
            $query->whereIn('source', ['facebook_lead_ads', 'tiktok_lead_ads', 'file_import', 'form_submission']);
        }

        if (! empty($options['lead_statuses'])) {
            $statuses = array_filter((array) $options['lead_statuses']);
            if ($statuses !== []) {
                $query->whereIn('lead_status', $statuses);
            }
        } elseif (! empty($options['lead_status'])) {
            $query->where('lead_status', $options['lead_status']);
        }

        if (! empty($options['lead_priority'])) {
            $query->where('priority', $options['lead_priority']);
        }

        if (! empty($options['lead_source']) && ! $integrationOnly) {
            $query->where('source', $options['lead_source']);
        }

        return $query->get()->map(fn (Lead $lead) => [
            'email' => strtolower((string) $lead->email),
            'name' => $lead->full_name,
            'lead_id' => $lead->id,
            'customer_id' => null,
            'form_entry_id' => null,
        ]);
    }

    /** @param  array<int, int>  $ids */
    private function fromCustomers(int $organizationId, array $options, array $ids = []): Collection
    {
        $query = Customer::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        return $query->get()->map(fn (Customer $customer) => [
            'email' => strtolower((string) $customer->email),
            'name' => $customer->name,
            'lead_id' => null,
            'customer_id' => $customer->id,
            'form_entry_id' => null,
        ]);
    }

    /** @param  array<int, int>  $ids */
    private function fromFormEntries(int $organizationId, array $options, array $ids = []): Collection
    {
        $query = FormEntry::query()->where('organization_id', $organizationId);

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        } elseif (! empty($options['form_id'])) {
            $query->where('form_id', $options['form_id']);
        }

        return $query->latest('submitted_at')->limit(5000)->get()
            ->map(function (FormEntry $entry) {
                $data = is_array($entry->data) ? $entry->data : [];
                $email = $data['email'] ?? null;
                $name = trim(($data['first_name'] ?? $data['fname'] ?? '').' '.($data['last_name'] ?? $data['lname'] ?? ''));

                return [
                    'email' => $email ? strtolower((string) $email) : '',
                    'name' => $name !== '' ? $name : null,
                    'lead_id' => null,
                    'customer_id' => null,
                    'form_entry_id' => $entry->id,
                ];
            })
            ->filter(fn ($row) => $row['email'] !== '');
    }

    private function fromManual(string $raw): Collection
    {
        $parts = preg_split('/[\n,;]+/', $raw) ?: [];

        return collect($parts)
            ->map(fn ($email) => [
                'email' => strtolower(trim($email)),
                'name' => null,
                'lead_id' => null,
                'customer_id' => null,
                'form_entry_id' => null,
            ])
            ->filter(fn ($row) => $row['email'] !== '');
    }
}
