<?php

namespace App\Services\Crm;

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
            $lead->refresh();

            if ($lead->is_converted && $lead->customer_id) {
                return $lead->customer()->firstOrFail();
            }

            $customer = Customer::create([
                'organization_id' => $lead->organization_id,
                'lead_id' => $lead->id,
                'form_entry_id' => $lead->form_entry_id,
                'name' => $lead->full_name,
                'email' => $lead->email ?? 'lead-'.$lead->id.'@placeholder.local',
                'phone' => $lead->phone,
                'company' => $lead->company,
                'address' => $lead->address,
                'city' => $lead->city,
                'state' => $lead->province,
                'zip_code' => $lead->postal_code,
                'status' => 'active',
                'source' => $lead->lead_source ?? $lead->source ?? 'lead_conversion',
                'assigned_to' => $lead->assigned_to,
                'created_by' => auth('admin')->id(),
            ]);

            $lead->update([
                'is_converted' => true,
                'converted_at' => now(),
                'lead_status' => 'won',
                'customer_id' => $customer->id,
            ]);

            $lead->logActivity('converted', 'Lead converted to customer #'.$customer->id);

            CustomerActivity::create([
                'organization_id' => $customer->organization_id,
                'customer_id' => $customer->id,
                'admin_id' => auth('admin')->id(),
                'type' => 'note',
                'subject' => 'Converted from Lead',
                'description' => 'Customer created from lead #'.$lead->id,
                'activity_date' => now(),
                'status' => 'completed',
            ]);

            return $customer;
        });
    }
}
