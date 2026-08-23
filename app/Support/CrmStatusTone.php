<?php

namespace App\Support;

/**
 * Shared CRM status/priority tone keys for pills and inline selects.
 */
final class CrmStatusTone
{
    public static function for(?string $value): string
    {
        $value = str_replace('-', '_', (string) $value);

        return match ($value) {
            'active', 'accepted', 'won', 'paid', 'completed', 'approved' => 'success',
            'sent', 'contacted', 'in_progress' => 'info',
            'pending', 'new', 'prospect', 'partially_paid', 'medium', 'expired' => 'warning',
            'overdue', 'urgent', 'high', 'on_hold' => 'caution',
            'rejected', 'lost', 'cancelled', 'inactive' => 'danger',
            'qualified', 'negotiation', 'proposal_sent', 'converted' => 'indigo',
            'draft', 'low', '' => 'neutral',
            default => 'neutral',
        };
    }
}
