<?php

namespace Tests\Unit\Crm;

use App\Models\Crm\Lead;
use App\Support\LeadFollowUpState;
use Carbon\Carbon;
use Tests\TestCase;

class LeadFollowUpStateTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_upcoming_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21 10:00:00', 'UTC'));
        $lead = new Lead([
            'next_follow_up_date' => '2026-08-25',
            'next_follow_up_time' => '16:28:00',
            'next_follow_up_type' => 'call',
        ]);

        $state = LeadFollowUpState::forLead($lead);
        $this->assertSame(LeadFollowUpState::UPCOMING, $state->state);
        $this->assertFalse($state->attention);
        $this->assertStringContainsString('Aug 25', $state->label);
    }

    public function test_due_soon_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21 10:00:00', 'UTC'));
        $lead = new Lead([
            'next_follow_up_date' => '2026-08-21',
            'next_follow_up_time' => '20:00:00',
        ]);

        $state = LeadFollowUpState::forLead($lead);
        $this->assertSame(LeadFollowUpState::DUE_SOON, $state->state);
        $this->assertStringContainsString('Due', $state->label);
    }

    public function test_due_now_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21 16:28:00', 'UTC'));
        $lead = new Lead([
            'next_follow_up_date' => '2026-08-21',
            'next_follow_up_time' => '16:30:00',
        ]);

        $state = LeadFollowUpState::forLead($lead);
        $this->assertSame(LeadFollowUpState::DUE_NOW, $state->state);
        $this->assertTrue($state->attention);
        $this->assertSame('Due now', $state->label);
    }

    public function test_overdue_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-21 18:00:00', 'UTC'));
        $lead = new Lead([
            'next_follow_up_date' => '2026-08-21',
            'next_follow_up_time' => '14:00:00',
        ]);

        $state = LeadFollowUpState::forLead($lead);
        $this->assertSame(LeadFollowUpState::OVERDUE, $state->state);
        $this->assertTrue($state->attention);
        $this->assertStringContainsString('Missed', $state->label);
    }

    public function test_none_when_no_follow_up(): void
    {
        $lead = new Lead([]);
        $state = LeadFollowUpState::forLead($lead);
        $this->assertSame(LeadFollowUpState::NONE, $state->state);
        $this->assertFalse($state->hasFollowUp());
    }
}
