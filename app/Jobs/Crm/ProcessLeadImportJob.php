<?php

namespace App\Jobs\Crm;

use App\Models\Crm\LeadImport;
use App\Services\Crm\LeadImport\LeadImportProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLeadImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $leadImportId) {}

    public function handle(LeadImportProcessor $processor): void
    {
        $import = LeadImport::query()->find($this->leadImportId);
        if (! $import) {
            return;
        }

        $processor->process($import);
    }
}
