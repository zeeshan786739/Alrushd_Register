<?php

namespace App\Services\EmailMarketing;

use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\EmailMarketing\Suppression;
use App\Models\FormEntry;
use Illuminate\Support\Collection;

class CampaignRecipientResolver
{
    /**
     * @param  array{source:string, lead_ids?:array<int>, customer_ids?:array<int>, form_entry_ids?:array<int>, manual_emails?:string, lead_status?:?string}  $options
     * @return Collection<int, array{email:string,name:?string,lead_id:?int,customer_id:?int,form_entry_id:?int}>
     */
    public function resolve(int $organizationId, array $options): Collection
    {
        $source = $options['source'] ?? 'manual';
        $rows = collect();

        match ($source) {
            'leads' => $rows = $this->fromLeads($organizationId, $options),
            'customers' => $rows = $this->fromCustomers($organizationId, $options),
            'form_entries' => $rows = $this->fromFormEntries($organizationId, $options),
            'selected_leads' => $rows = $this->fromLeads($organizationId, $options, $options['lead_ids'] ?? []),
            'selected_customers' => $rows = $this->fromCustomers($organizationId, $options, $options['customer_ids'] ?? []),
            default => $rows = $this->fromManual($options['manual_emails'] ?? ''),
        };

        $suppressed = Suppression::query()
            ->where('organization_id', $organizationId)
            ->pluck('email')
            ->map(fn ($e) => strtolower($e))
            ->all();

        return $rows
            ->filter(fn ($row) => filter_var($row['email'], FILTER_VALIDATE_EMAIL))
            ->reject(fn ($row) => in_array(strtolower($row['email']), $suppressed, true))
            ->unique(fn ($row) => strtolower($row['email']))
            ->values();
    }

    /** @param  array<int, int>  $ids */
    private function fromLeads(int $organizationId, array $options, array $ids = []): Collection
    {
        $query = Lead::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        if (! empty($options['lead_status'])) {
            $query->where('lead_status', $options['lead_status']);
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
            ->whereNotNull('email');

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

    private function fromFormEntries(int $organizationId, array $options): Collection
    {
        return FormEntry::query()
            ->where('organization_id', $organizationId)
            ->latest('submitted_at')
            ->limit(2000)
            ->get()
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
