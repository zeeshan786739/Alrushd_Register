<?php

namespace Tests\Unit\Crm;

use App\Enums\LeadStatus;
use App\Models\Organization;
use App\Support\LeadPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordered_statuses_default_to_enum_order(): void
    {
        $organization = Organization::create([
            'name' => 'Pipeline Org',
            'slug' => 'pipeline-org',
            'is_active' => true,
        ]);

        $this->assertSame(
            LeadStatus::cases(),
            LeadPipeline::orderedStatuses($organization)
        );
    }

    public function test_save_order_persists_and_appends_missing_statuses(): void
    {
        $organization = Organization::create([
            'name' => 'Pipeline Org',
            'slug' => 'pipeline-org-2',
            'is_active' => true,
        ]);

        $partial = ['qualified', 'new', 'contacted'];
        $ordered = LeadPipeline::saveOrder($organization, $partial);
        $values = array_map(static fn (LeadStatus $status) => $status->value, $ordered);

        $this->assertSame('qualified', $values[0]);
        $this->assertSame('new', $values[1]);
        $this->assertSame('contacted', $values[2]);
        $this->assertSame(count(LeadStatus::cases()), count($values));
        $this->assertSame(count(LeadStatus::cases()), count(array_unique($values)));
    }
}
