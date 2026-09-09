<?php

namespace App\Observers;

use App\Models\FormEntry;
use App\Services\Crm\FormEntryLeadConverter;

class FormEntryObserver
{
    public function __construct(private FormEntryLeadConverter $converter) {}

    public function created(FormEntry $formEntry): void
    {
        if (! $this->converter->shouldAutoConvert($formEntry)) {
            return;
        }

        $this->converter->convertIfMissing($formEntry);
    }
}
