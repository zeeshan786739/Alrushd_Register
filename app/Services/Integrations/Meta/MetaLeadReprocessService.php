<?php

namespace App\Services\Integrations\Meta;

use App\Enums\MetaLeadSubmissionStatus;
use App\Models\Integrations\MetaLeadSubmission;
use App\Jobs\Integrations\ProcessMetaLeadJob;

class MetaLeadReprocessService
{
    public function reprocess(MetaLeadSubmission $submission): void
    {
        if ($submission->status === MetaLeadSubmissionStatus::Processed && $submission->lead_id) {
            return;
        }

        $submission->update([
            'status' => MetaLeadSubmissionStatus::Pending,
            'error_message' => null,
            'processed_at' => null,
        ]);

        ProcessMetaLeadJob::dispatch($submission->id);
    }

    /** @return int Number of submissions queued */
    public function reprocessPendingForOrganization(int $organizationId): int
    {
        $submissions = MetaLeadSubmission::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                MetaLeadSubmissionStatus::Unmapped->value,
                MetaLeadSubmissionStatus::Failed->value,
                MetaLeadSubmissionStatus::Pending->value,
            ])
            ->get();

        foreach ($submissions as $submission) {
            $this->reprocess($submission);
        }

        return $submissions->count();
    }
}
