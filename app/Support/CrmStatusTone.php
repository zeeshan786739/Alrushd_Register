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

    /**
     * Iconify icon for status/priority values (not used inside native <option>).
     */
    public static function icon(?string $value): string
    {
        $value = str_replace('-', '_', (string) $value);

        return match ($value) {
            'new', 'pending', 'prospect' => 'solar:clock-circle-linear',
            'contacted', 'sent', 'in_progress' => 'solar:chat-round-dots-linear',
            'qualified', 'negotiation', 'proposal_sent', 'converted' => 'solar:verified-check-linear',
            'won', 'completed', 'active', 'accepted', 'paid', 'approved' => 'solar:check-circle-linear',
            'lost', 'rejected', 'cancelled', 'inactive' => 'solar:close-circle-linear',
            'on_hold', 'expired', 'partially_paid' => 'solar:pause-circle-linear',
            'draft' => 'solar:document-linear',
            'low' => 'solar:arrow-down-linear',
            'medium' => 'solar:minus-circle-linear',
            'high' => 'solar:arrow-up-linear',
            'urgent', 'overdue' => 'solar:danger-triangle-linear',
            default => 'solar:record-circle-linear',
        };
    }
}
