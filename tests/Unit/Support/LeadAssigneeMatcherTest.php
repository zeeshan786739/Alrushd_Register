<?php

namespace Tests\Unit\Support;

use App\Models\Admin;
use App\Models\Organization;
use App\Support\LeadAssigneeMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAssigneeMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_matches_lead_email_prefix_to_teammate_first_name(): void
    {
        $organizationId = Organization::create(['name' => 'Matcher Org', 'slug' => 'matcher-org', 'is_active' => true])->id;

        Admin::create([
            'name' => 'Foysol Ahmed',
            'email' => 'foysol@example.test',
            'password' => bcrypt('password'),
            'organization_id' => $organizationId,
        ]);

        Admin::create([
            'name' => 'Tayyeb Khan',
            'email' => 'tayyeb@example.test',
            'password' => bcrypt('password'),
            'organization_id' => $organizationId,
        ]);

        $matcher = new LeadAssigneeMatcher;
        $query = Admin::query()->where('organization_id', $organizationId);

        $foysol = $matcher->matchByLeadEmail($query, 'foysol11@gmail.com');
        $this->assertNotNull($foysol);
        $this->assertSame('Foysol Ahmed', $foysol->name);

        $tayyeb = $matcher->matchByLeadEmail($query, 'tayyeb.parent@example.com');
        $this->assertNotNull($tayyeb);
        $this->assertSame('Tayyeb Khan', $tayyeb->name);
    }

    public function test_matches_fuzzy_spelling_variants(): void
    {
        $organizationId = Organization::create(['name' => 'Matcher Org', 'slug' => 'matcher-org', 'is_active' => true])->id;

        Admin::create([
            'name' => 'Foysal Ahmed',
            'email' => 'foysal@example.test',
            'password' => bcrypt('password'),
            'organization_id' => $organizationId,
        ]);

        $matcher = new LeadAssigneeMatcher;
        $match = $matcher->matchByLeadEmail(
            Admin::query()->where('organization_id', $organizationId),
            'foysol11@gmail.com'
        );

        $this->assertNotNull($match);
        $this->assertSame('Foysal Ahmed', $match->name);
    }
}
