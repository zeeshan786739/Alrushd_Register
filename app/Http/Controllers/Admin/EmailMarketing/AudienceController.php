<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\EmailMarketing\CampaignPreflightService;
use App\Support\LeadSourceOptions;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AudienceController extends Controller
{
    public function __construct(private CampaignPreflightService $preflight)
    {
        $this->middleware('permission:send campaigns|create campaigns|compose emails');
    }

    public function search(Request $request): JsonResponse
    {
        $type = $request->get('type', 'leads');
        $query = trim((string) $request->get('q', ''));
        $organizationId = OrganizationContext::idOrFail();
        $limit = min(50, max(5, (int) $request->get('limit', 20)));

        $rows = match ($type) {
            'customers' => $this->searchCustomers($organizationId, $query, $limit),
            'forms' => $this->searchFormEntries($organizationId, $query, $limit, $request->integer('form_id') ?: null),
            default => $this->searchLeads($organizationId, $query, $limit, $request->get('lead_source')),
        };

        return response()->json(['data' => $rows]);
    }

    public function preflight(Request $request): JsonResponse
    {
        $options = $this->audienceOptions($request);
        $summary = $this->preflight->summarize(OrganizationContext::idOrFail(), $options);

        return response()->json([
            'selected' => $summary['selected'],
            'valid' => $summary['valid'],
            'invalid' => $summary['invalid'],
            'duplicates' => $summary['duplicates'],
            'unsubscribed' => $summary['unsubscribed'],
            'suppressed' => $summary['suppressed'],
            'eligible' => $summary['eligible'],
            'sample' => $summary['eligible_rows']->take(8)->values(),
        ]);
    }

    public function forms(): JsonResponse
    {
        $forms = Form::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $forms]);
    }

    /** @return array<string, mixed> */
    private function audienceOptions(Request $request): array
    {
        return [
            'source' => $request->get('recipient_source', 'manual'),
            'manual_emails' => $request->get('manual_emails'),
            'lead_ids' => array_filter(array_map('intval', (array) $request->input('lead_ids', []))),
            'customer_ids' => array_filter(array_map('intval', (array) $request->input('customer_ids', []))),
            'form_entry_ids' => array_filter(array_map('intval', (array) $request->input('form_entry_ids', []))),
            'lead_status' => $request->get('lead_status'),
            'lead_statuses' => array_filter((array) $request->input('lead_statuses', [])),
            'lead_priority' => $request->get('lead_priority'),
            'lead_source' => $request->get('lead_source'),
            'form_id' => $request->integer('form_id') ?: null,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function searchLeads(int $organizationId, string $query, int $limit, ?string $source): array
    {
        $builder = Lead::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($source) {
            $builder->where('source', $source);
        }

        if ($query !== '') {
            $builder->where(function ($inner) use ($query) {
                $inner->where('email', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('company', 'like', "%{$query}%");
            });
        }

        return $builder->latest('updated_at')->limit($limit)->get()->map(fn (Lead $lead) => [
            'id' => $lead->id,
            'type' => 'lead',
            'name' => $lead->full_name,
            'email' => $lead->email,
            'meta' => LeadSourceOptions::label($lead->source).' · '.ucfirst((string) $lead->lead_status),
            'source' => $lead->source,
        ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function searchCustomers(int $organizationId, string $query, int $limit): array
    {
        $builder = Customer::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($query !== '') {
            $builder->where(function ($inner) use ($query) {
                $inner->where('email', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('company', 'like', "%{$query}%");
            });
        }

        return $builder->latest('updated_at')->limit($limit)->get()->map(fn (Customer $customer) => [
            'id' => $customer->id,
            'type' => 'customer',
            'name' => $customer->name,
            'email' => $customer->email,
            'meta' => 'Customer',
            'source' => 'customer',
        ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function searchFormEntries(int $organizationId, string $query, int $limit, ?int $formId): array
    {
        $builder = FormEntry::query()
            ->with('form:id,name')
            ->where('organization_id', $organizationId);

        if ($formId) {
            $builder->where('form_id', $formId);
        }

        return $builder->latest('submitted_at')->limit($limit)->get()->map(function (FormEntry $entry) use ($query) {
            $data = is_array($entry->data) ? $entry->data : [];
            $email = strtolower((string) ($data['email'] ?? ''));
            $name = trim(($data['first_name'] ?? $data['fname'] ?? '').' '.($data['last_name'] ?? $data['lname'] ?? ''));

            if ($query !== '' && ! str_contains($email, strtolower($query)) && ! str_contains(strtolower($name), strtolower($query))) {
                return null;
            }

            if ($email === '') {
                return null;
            }

            return [
                'id' => $entry->id,
                'type' => 'form_entry',
                'name' => $name !== '' ? $name : $email,
                'email' => $email,
                'meta' => ($entry->form?->name ?? 'Form').' · '.$entry->submitted_at?->diffForHumans(),
                'source' => 'form_submission',
            ];
        })->filter()->values()->all();
    }
}
