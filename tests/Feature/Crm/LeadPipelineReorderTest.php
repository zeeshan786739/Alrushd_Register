<?php

namespace Tests\Feature\Crm;

use App\Enums\LeadStatus;
use App\Support\LeadPipeline;

class LeadPipelineReorderTest extends CrmTestCase
{
    public function test_admin_can_reorder_pipeline_columns(): void
    {
        $statuses = array_column(LeadStatus::cases(), 'value');
        $reordered = array_values(array_unique(array_merge(
            ['qualified', 'contacted', 'new'],
            $statuses
        )));

        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.reorder-pipeline-columns'), [
                'statuses' => $reordered,
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'statuses' => $reordered,
            ]);

        $this->organizationA->refresh();
        $this->assertSame(
            $reordered,
            $this->organizationA->metadata[LeadPipeline::METADATA_KEY]
        );

        $orderedValues = array_map(
            static fn (LeadStatus $status) => $status->value,
            LeadPipeline::orderedStatuses($this->organizationA)
        );
        $this->assertSame($reordered, $orderedValues);
    }

    public function test_pipeline_reorder_rejects_incomplete_status_list(): void
    {
        $this->actingAsCrmAdmin()
            ->patchJson(route('admin.crm.leads.reorder-pipeline-columns'), [
                'statuses' => ['new', 'contacted'],
            ])
            ->assertStatus(422);
    }

    public function test_board_view_uses_saved_pipeline_order(): void
    {
        $statuses = array_column(LeadStatus::cases(), 'value');
        $reordered = array_values(array_unique(array_merge(
            ['won', 'new', 'contacted'],
            $statuses
        )));

        LeadPipeline::saveOrder($this->organizationA, $reordered);

        $html = $this->actingAsCrmAdmin()
            ->get(route('admin.crm.leads.index', ['view' => 'board']))
            ->assertOk()
            ->getContent();

        $positions = [];
        foreach ($reordered as $status) {
            $positions[$status] = strpos($html, 'data-status="'.$status.'"');
            $this->assertNotFalse($positions[$status], "Missing column {$status}");
        }

        $this->assertTrue($positions['won'] < $positions['new']);
        $this->assertTrue($positions['new'] < $positions['contacted']);
    }
}
