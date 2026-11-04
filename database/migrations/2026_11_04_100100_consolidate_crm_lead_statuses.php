<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Reduces the lead pipeline to New, Pending, Enrolled, Not Interested and Spam.
 *
 * Every lead's previous status is kept in legacy_lead_status, so down() can
 * restore it exactly. The map is deliberately duplicated here rather than read
 * from App\Enums\LeadStatus: a migration must not change when app code changes.
 */
return new class extends Migration
{
    private const STATUS_MAP = [
        'contacted' => 'pending',
        'proposal_sent' => 'pending',
        'negotiation' => 'pending',
        'on_hold' => 'pending',
        'won' => 'enrolled',
        'qualified' => 'enrolled',
        'lost' => 'not_interested',
    ];

    private const STATUSES = ['new', 'pending', 'enrolled', 'not_interested', 'spam'];

    /** Custom statuses a team added by hand that mean the same as a built-in one. */
    private const CUSTOM_SYNONYMS = [
        'new' => 'new',
        'pending' => 'pending',
        'enrolled' => 'enrolled',
        'enroled' => 'enrolled',
        'enrollment' => 'enrolled',
        'enrolment' => 'enrolled',
        'not_interested' => 'not_interested',
        'spam' => 'spam',
    ];

    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('legacy_lead_status')->nullable()->after('lead_status');
        });

        DB::transaction(fn () => $this->consolidate());
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::table('crm_leads')
                ->whereNotNull('legacy_lead_status')
                ->update(['lead_status' => DB::raw('legacy_lead_status')]);

            if (Schema::hasTable('crm_lead_statuses')) {
                DB::table('crm_lead_statuses')->where('is_active', false)->update(['is_active' => true]);
            }

            DB::table('organizations')->get(['id', 'metadata'])->each(function ($organization) {
                $metadata = json_decode((string) $organization->metadata, true);
                if (is_array($metadata) && array_key_exists('crm_pipeline_status_order', $metadata)) {
                    unset($metadata['crm_pipeline_status_order']);
                    DB::table('organizations')->where('id', $organization->id)->update(['metadata' => json_encode($metadata)]);
                }
            });
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn('legacy_lead_status');
        });
    }

    /** Includes soft-deleted leads, so restoring one never brings back a retired status. */
    public function consolidate(): void
    {
        $moved = [];

        foreach (self::STATUS_MAP as $old => $new) {
            $moved[$old] = $this->moveLeads(DB::table('crm_leads')->where('lead_status', $old), $new);
        }

        $this->retireCustomStatuses($moved);

        // Nothing may be left outside the five, or that lead would have no column on the board.
        $moved['other'] = $this->moveLeads(DB::table('crm_leads')->whereNotIn('lead_status', self::STATUSES), 'pending');

        $this->migrateSavedFilters();
        $this->migrateCampaignAudiences();
        $this->migratePipelineOrder();

        Log::info('Lead statuses consolidated.', ['moved' => array_filter($moved)]);
    }

    private function moveLeads($query, string $target): int
    {
        return $query->update([
            'legacy_lead_status' => DB::raw('lead_status'),
            'lead_status' => $target,
        ]);
    }

    /** @param  array<string, int>  $moved */
    private function retireCustomStatuses(array &$moved): void
    {
        if (! Schema::hasTable('crm_lead_statuses')) {
            return;
        }

        foreach (DB::table('crm_lead_statuses')->get() as $custom) {
            $target = $this->targetForCustomStatus($custom->slug, $custom->name);

            if ($custom->slug !== $target) {
                $moved[$custom->slug] = $this->moveLeads(
                    DB::table('crm_leads')
                        ->where('organization_id', $custom->organization_id)
                        ->where('lead_status', $custom->slug),
                    $target
                );
            }

            // Deactivated rather than deleted, so down() can bring it back.
            DB::table('crm_lead_statuses')->where('id', $custom->id)->update(['is_active' => false]);
        }
    }

    /** A status with no direct equivalent is still an open conversation, so it becomes Pending. */
    private function targetForCustomStatus(string $slug, string $name): string
    {
        foreach ([$slug, $name] as $candidate) {
            $key = str_replace([' ', '-'], '_', mb_strtolower(trim($candidate)));
            $target = self::CUSTOM_SYNONYMS[$key] ?? self::STATUS_MAP[$key] ?? null;
            if ($target !== null) {
                return $target;
            }
        }

        return 'pending';
    }

    private function mapStatus(string $status): string
    {
        return $this->targetForCustomStatus($status, $status);
    }

    private function migrateSavedFilters(): void
    {
        if (! Schema::hasTable('crm_saved_filters')) {
            return;
        }

        DB::table('crm_saved_filters')->where('module', 'leads')->get(['id', 'filters'])->each(function ($row) {
            $filters = json_decode((string) $row->filters, true);
            if (! is_array($filters)) {
                return;
            }

            unset($filters['priority']);
            if (! empty($filters['lead_status']) && is_string($filters['lead_status'])) {
                $filters['lead_status'] = $this->mapStatus($filters['lead_status']);
            }

            DB::table('crm_saved_filters')->where('id', $row->id)->update(['filters' => json_encode($filters)]);
        });
    }

    /** Only campaigns yet to send: a sent campaign's audience is a historical record. */
    private function migrateCampaignAudiences(): void
    {
        if (! Schema::hasTable('em_campaigns')) {
            return;
        }

        DB::table('em_campaigns')
            ->whereIn('status', ['draft', 'scheduled'])
            ->whereNotNull('recipient_filters')
            ->get(['id', 'recipient_filters'])
            ->each(function ($row) {
                $filters = json_decode((string) $row->recipient_filters, true);
                if (! is_array($filters)) {
                    return;
                }

                unset($filters['lead_priority']);
                if (! empty($filters['lead_status']) && is_string($filters['lead_status'])) {
                    $filters['lead_status'] = $this->mapStatus($filters['lead_status']);
                }
                if (! empty($filters['lead_statuses']) && is_array($filters['lead_statuses'])) {
                    $filters['lead_statuses'] = array_values(array_unique(array_map(
                        fn ($status) => $this->mapStatus((string) $status),
                        $filters['lead_statuses']
                    )));
                }

                DB::table('em_campaigns')->where('id', $row->id)->update(['recipient_filters' => json_encode($filters)]);
            });
    }

    private function migratePipelineOrder(): void
    {
        DB::table('organizations')->get(['id', 'metadata'])->each(function ($organization) {
            $metadata = json_decode((string) $organization->metadata, true);
            $order = is_array($metadata) ? ($metadata['crm_pipeline_status_order'] ?? null) : null;
            if (! is_array($order)) {
                return;
            }

            $metadata['crm_pipeline_status_order'] = array_values(array_unique(array_map(
                fn ($status) => $this->mapStatus((string) $status),
                $order
            )));

            DB::table('organizations')->where('id', $organization->id)->update(['metadata' => json_encode($metadata)]);
        });
    }
};
