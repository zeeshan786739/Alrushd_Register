<?php

namespace Tests\Unit\Crm;

use App\Support\ImportedLeadDetails;
use App\Services\Crm\LeadImport\LeadImportHeaderNormalizer;
use App\Services\Crm\LeadImport\LeadImportMappingService;
use App\Support\LeadImportFields;
use PHPUnit\Framework\TestCase;

class ImportedLeadDetailsTest extends TestCase
{
    public function test_all_original_cells_are_visible_including_empty_duplicate_and_multiline_values(): void
    {
        $headers = [
            ['key' => 'col_0', 'label' => 'Student Name'],
            ['key' => 'col_1', 'label' => 'Notes:'],
            ['key' => 'col_2', 'label' => 'Notes:'],
            ['key' => 'col_3', 'label' => 'Admission Fee'],
            ['key' => 'col_4', 'label' => 'Lead Date'],
        ];
        $groups = ImportedLeadDetails::group($headers, ['col_0' => 'Student', 'col_1' => "First call\nSecond call", 'col_2' => null, 'col_3' => 0, 'col_4' => 46027]);
        $items = array_merge(...array_values($groups));
        $this->assertCount(5, $items);
        $values = array_column($items, 'value');
        $this->assertContains("First call\nSecond call", $values);
        $this->assertContains('Not provided', $values);
        $this->assertContains('0', $values);
        $this->assertContains('05 Jan 2026 00:00', $values);
    }

    public function test_admission_fields_are_not_misclassified_as_form_names_or_follow_up_dates(): void
    {
        $headers = [];
        foreach (['Application Form', 'Direct Debit form', 'Followup details', 'Admission Fee', 'Current status', 'Lead Status'] as $index => $label) {
            $headers[] = ['key' => 'col_'.$index, 'label' => $label, 'index' => $index];
        }
        $mapping = (new LeadImportMappingService(new LeadImportHeaderNormalizer))->suggest($headers);
        foreach ([0, 1, 2, 3, 5] as $index) $this->assertSame(LeadImportFields::CUSTOM, $mapping['col_'.$index]['field']);
        $this->assertSame('lead_status', $mapping['col_4']['field']);
    }
}
