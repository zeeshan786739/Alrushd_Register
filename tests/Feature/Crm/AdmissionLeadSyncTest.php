<?php

namespace Tests\Feature\Crm;

use App\Models\Crm\Lead;
use App\Models\FormSubmission;
use App\Models\Organization;
use App\Services\Crm\LeadSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdmissionLeadSyncTest extends CrmTestCase
{
    public function test_admission_sync_creates_crm_lead_idempotently(): void
    {
        Organization::default();

        $submission = $this->createFormSubmission([
            'fname' => 'Sarah',
            'lname' => 'Connor',
            'email' => 'sarah@example.com',
            'mobile_number' => '1234567890',
            'selected_school' => 'Primary',
            'status' => 'paid',
            'total_amount' => 500,
        ]);

        $service = app(LeadSyncService::class);
        $lead1 = $service->syncFromFormSubmission($submission);
        $lead2 = $service->syncFromFormSubmission($submission->fresh());

        $this->assertSame($lead1->id, $lead2->id);
        $this->assertSame(1, Lead::where('form_submission_id', $submission->id)->count());
        $this->assertSame('student_admission', $lead1->source);
        $this->assertSame('Sarah', $lead1->first_name);
        $this->assertSame('sarah@example.com', $lead1->email);
        $this->assertTrue($lead1->activities()->where('activity_type', 'synced')->exists());
    }

    public function test_repeated_sync_updates_existing_lead_without_duplicates(): void
    {
        Organization::default();

        $submission = $this->createFormSubmission([
            'fname' => 'Initial',
            'email' => 'initial@example.com',
            'status' => 'pending_payment',
        ]);

        $service = app(LeadSyncService::class);
        $service->syncFromFormSubmission($submission);

        $submission->update(['fname' => 'Updated', 'status' => 'paid']);
        $lead = $service->syncFromFormSubmission($submission->fresh());

        $this->assertSame('Updated', $lead->first_name);
        $this->assertSame(1, Lead::count());
    }

    /** @param  array<string, mixed>  $attributes */
    protected function createFormSubmission(array $attributes = []): FormSubmission
    {
        $payload = array_merge([
            'fname' => 'Test',
            'email' => 'test@example.com',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes);

        // Fresh installs still have the historical self-FK column; production clones do not.
        if (Schema::hasColumn('form_submissions', 'form_submission_id')) {
            Schema::disableForeignKeyConstraints();
            $payload['form_submission_id'] = $payload['form_submission_id'] ?? 1;
            $id = DB::table('form_submissions')->insertGetId($payload);
            DB::table('form_submissions')->where('id', $id)->update(['form_submission_id' => $id]);
            Schema::enableForeignKeyConstraints();
        } else {
            $id = DB::table('form_submissions')->insertGetId($payload);
        }

        return FormSubmission::findOrFail($id);
    }
}
