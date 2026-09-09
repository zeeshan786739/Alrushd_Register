<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadImportRowStatus;
use App\Enums\LeadImportStatus;
use App\Models\Admin;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadImport;
use App\Models\Crm\LeadImportRow;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeadImportUndoService
{
    /**
     * Soft-remove every lead created by this import batch from the CRM UI.
     * Leads are never hard-deleted; import rows are marked as undone for history.
     *
     * @return array{undone: int, skipped_converted: int, already_removed: int}
     */
    public function undo(LeadImport $import, Admin $admin): array
    {
        if ($import->statusEnum() === LeadImportStatus::Undone) {
            throw new RuntimeException('This import has already been undone.');
        }

        if ($import->statusEnum() !== LeadImportStatus::Completed) {
            throw new RuntimeException('Only completed imports can be undone.');
        }

        if ((int) $import->imported_rows <= 0) {
            throw new RuntimeException('This import did not create any leads to undo.');
        }

        $stats = ['undone' => 0, 'skipped_converted' => 0, 'already_removed' => 0];

        DB::transaction(function () use ($import, $admin, &$stats) {
            $import->rows()
                ->where('status', LeadImportRowStatus::Imported->value)
                ->whereNotNull('lead_id')
                ->orderBy('row_number')
                ->chunkById(100, function ($rows) use (&$stats) {
                    foreach ($rows as $row) {
                        $this->undoRow($row, $stats);
                    }
                });

            Lead::query()
                ->where('organization_id', $import->organization_id)
                ->where('lead_import_id', $import->id)
                ->where('is_converted', false)
                ->orderBy('id')
                ->chunkById(100, function ($leads) use ($import, &$stats) {
                    foreach ($leads as $lead) {
                        $this->undoLead($lead, $import->id, $stats);
                    }
                });

            $import->update([
                'status' => LeadImportStatus::Undone->value,
                'undone_rows' => max($stats['undone'], (int) $import->undone_rows),
                'undone_by' => $admin->id,
                'undone_at' => now(),
            ]);
        });

        return $stats;
    }

    /**
     * Undo every completed import batch for the current organization.
     *
     * @return array{batches: int, undone: int, skipped_converted: int, already_removed: int, failed_batches: int}
     */
    public function undoAll(Admin $admin): array
    {
        $imports = LeadImport::forCurrentOrganization()
            ->where('status', LeadImportStatus::Completed->value)
            ->where('imported_rows', '>', 0)
            ->orderBy('id')
            ->get();

        $totals = [
            'batches' => 0,
            'undone' => 0,
            'skipped_converted' => 0,
            'already_removed' => 0,
            'failed_batches' => 0,
        ];

        foreach ($imports as $import) {
            try {
                $stats = $this->undo($import->fresh(), $admin);
                $totals['batches']++;
                $totals['undone'] += $stats['undone'];
                $totals['skipped_converted'] += $stats['skipped_converted'];
                $totals['already_removed'] += $stats['already_removed'];
            } catch (RuntimeException) {
                $totals['failed_batches']++;
            }
        }

        if ($totals['batches'] === 0 && $totals['failed_batches'] === 0) {
            throw new RuntimeException('There are no completed imports with leads to remove.');
        }

        return $totals;
    }

    /** @return array{active_leads: int, undoable_batches: int} */
    public function removableSummary(): array
    {
        $undoableBatches = LeadImport::forCurrentOrganization()
            ->where('status', LeadImportStatus::Completed->value)
            ->where('imported_rows', '>', 0)
            ->count();

        $activeLeads = Lead::forCurrentOrganization()
            ->fromImport()
            ->count();

        return [
            'active_leads' => $activeLeads,
            'undoable_batches' => $undoableBatches,
        ];
    }

    /** @param array{undone: int, skipped_converted: int, already_removed: int} $stats */
    private function undoRow(LeadImportRow $row, array &$stats): void
    {
        $lead = Lead::withTrashed()->find($row->lead_id);
        if (! $lead) {
            $row->update(['status' => LeadImportRowStatus::Undone->value]);

            return;
        }

        $this->undoLead($lead, $row->lead_import_id, $stats, $row);
    }

    /** @param array{undone: int, skipped_converted: int, already_removed: int} $stats */
    private function undoLead(Lead $lead, int $importId, array &$stats, ?LeadImportRow $row = null): void
    {
        if ($lead->trashed()) {
            $stats['already_removed']++;
            $row?->update(['status' => LeadImportRowStatus::Undone->value]);

            return;
        }

        if ($lead->is_converted) {
            $stats['skipped_converted']++;

            return;
        }

        $lead->logActivity('archived', 'Lead removed from CRM view (import batch undone)', [
            'lead_import_id' => $importId,
            'row_number' => $row?->row_number,
        ]);
        $lead->delete();

        $row?->update(['status' => LeadImportRowStatus::Undone->value]);
        $stats['undone']++;
    }
}
