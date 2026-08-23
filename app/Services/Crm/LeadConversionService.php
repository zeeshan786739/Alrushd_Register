<?php

namespace App\Services\Crm;

use App\Models\Admin;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\Lead;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    public function convertToCustomer(Lead $lead): Customer
    {
        if ($lead->is_converted && $lead->customer_id) {
            return $lead->customer()->firstOrFail();
        }

        return DB::transaction(function () use ($lead) {
            $lockedLead = Lead::query()
                ->whereKey($lead->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLead->is_converted && $lockedLead->customer_id) {
                return $lockedLead->customer()->firstOrFail();
            }

            $email = $this->resolveCustomerEmail($lockedLead);
            $this->assertEmailAvailable($lockedLead->organization_id, $email);

            $assignedTo = $lockedLead->assigned_to;
            if ($assignedTo && ! Admin::query()
                ->where('organization_id', $lockedLead->organization_id)
                ->whereKey($assignedTo)
                ->exists()) {
                $assignedTo = null;
            }

            $customer = Customer::create([
                'organization_id' => $lockedLead->organization_id,
                'lead_id' => $lockedLead->id,
                'form_entry_id' => $lockedLead->form_entry_id,
                'name' => $lockedLead->full_name,
                'email' => $email,
                'phone' => $lockedLead->phone,
                'company' => $lockedLead->company,
                'address' => $lockedLead->address,
                'city' => $lockedLead->city,
                'state' => $lockedLead->province,
                'zip_code' => $lockedLead->postal_code,
                'status' => 'active',
                'source' => $lockedLead->lead_source ?? $lockedLead->source ?? 'lead_conversion',
                'assigned_to' => $assignedTo,
                'created_by' => auth('admin')->id(),
            ]);

            $lockedLead->update([
                'is_converted' => true,
                'converted_at' => now(),
                'lead_status' => 'won',
                'customer_id' => $customer->id,
            ]);

            $lockedLead->logActivity('converted', 'Lead converted to customer #'.$customer->id);

            CustomerActivity::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'admin_id' => auth('admin')->id(),
                'type' => 'note',
                'subject' => 'Converted from Lead',
                'description' => 'Customer created from lead #'.$lockedLead->id,
                'activity_date' => now(),
                'status' => 'completed',
            ]);

            return $customer;
        });
    }

    private function resolveCustomerEmail(Lead $lead): string
    {
        $email = strtolower(trim((string) ($lead->email ?? '')));

        if ($email === '') {
            return 'lead-'.$lead->id.'@placeholder.local';
        }

        return $email;
    }

    private function assertEmailAvailable(int $organizationId, string $email): void
    {
        $existing = Customer::withTrashed()
            ->where('organization_id', $organizationId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $existing) {
            return;
        }

        if ($existing->trashed()) {
            throw new LeadConversionException(
                'A soft-deleted customer with this email already exists in this organization. Resolve that customer record before converting this lead. The lead was not changed.'
            );
        }

        throw new LeadConversionException(
            'A customer with this email already exists in this organization. Conversion was cancelled; the lead was not changed.'
        );
    }
}
