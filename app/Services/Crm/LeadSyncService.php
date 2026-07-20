<?php

namespace App\Services\Crm;

use App\Models\Crm\Lead;
use App\Models\FormSubmission;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class LeadSyncService
{
    public function syncFromFormSubmission(FormSubmission $submission, ?int $organizationId = null): Lead
    {
        $organizationId ??= Organization::default()->id;

        return DB::transaction(function () use ($submission, $organizationId) {
            $lead = Lead::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'form_submission_id' => $submission->id,
                ],
                [
                    'source' => 'student_admission',
                    'lead_source' => 'Student Admission',
                    'title' => $submission->title,
                    'first_name' => $submission->fname ?? 'Unknown',
                    'last_name' => $submission->lname,
                    'email' => $submission->email,
                    'phone' => $submission->mobile_number ?? $submission->home_telephone,
                    'selected_school' => $submission->selected_school,
                    'lead_status' => 'new',
                    'priority' => 'medium',
                    'lead_description' => $this->buildDescription($submission),
                ]
            );

            if ($lead->wasRecentlyCreated) {
                $lead->activities()->create([
                    'organization_id' => $organizationId,
                    'activity_type' => 'synced',
                    'description' => 'Lead synced from student admission #'.$submission->id,
                ]);
            }

            return $lead->fresh();
        });
    }

    private function buildDescription(FormSubmission $submission): string
    {
        $parts = array_filter([
            $submission->selected_school ? 'School: '.$submission->selected_school : null,
            $submission->status ? 'Admission status: '.$submission->status : null,
            $submission->total_amount ? 'Amount: '.$submission->total_amount : null,
        ]);

        return $parts ? implode(' | ', $parts) : 'Synced from student admission form.';
    }
}
