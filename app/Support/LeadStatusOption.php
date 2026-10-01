<?php

namespace App\Support;

/**
 * Immutable status option used by list/board UI and forms.
 * Wraps both system enum statuses and org-custom statuses.
 */
final readonly class LeadStatusOption
{
    public function __construct(
        public string $value,
        private string $labelText,
        public string $tone = 'neutral',
        public bool $isCustom = false,
    ) {}

    public function label(): string
    {
        return $this->labelText;
    }
}
