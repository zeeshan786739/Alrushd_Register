<?php

namespace Tests\Unit\Crm;

use App\Services\Crm\LeadImport\LeadImportFileReader;
use App\Services\Crm\LeadImport\LeadImportHeaderNormalizer;
use App\Services\Crm\LeadImport\LeadImportMappingService;
use App\Services\Crm\LeadImport\LeadImportValueNormalizer;
use PHPUnit\Framework\TestCase;
use Tests\Support\LeadImportFixtureFactory;

class LeadImportReaderTest extends TestCase
{
    public function test_detects_spreadsheetml_despite_xls_extension(): void
    {
        $path = sys_get_temp_dir().'/web-ml-'.uniqid().'.xls';
        LeadImportFixtureFactory::webLeadsSpreadsheetMl($path);
        $format = (new LeadImportFileReader)->detectFormat($path);
        @unlink($path);
        $this->assertSame('spreadsheetml', $format);
    }

    public function test_header_normalizer_is_unicode_safe(): void
    {
        $normalizer = new LeadImportHeaderNormalizer;
        $this->assertSame('parent_lead_name', $normalizer->normalize("Parent / Lead Name"));
        $this->assertSame('follow_up', $normalizer->normalize("Follow Up"));
        $this->assertSame('follow_up', $normalizer->normalize("Follow up"));
    }

    public function test_administration_sheet_skips_legend_rows_and_maps_assignee_and_student(): void
    {
        $path = sys_get_temp_dir().'/administration-'.uniqid().'.xlsx';
        LeadImportFixtureFactory::administrationSheetXlsx($path);

        $parsed = (new LeadImportFileReader)->read($path);
        $mapping = (new LeadImportMappingService(new LeadImportHeaderNormalizer))
            ->suggest($parsed['headers'], $parsed['sample_values']);
        @unlink($path);

        $this->assertSame(12, $parsed['data_start_row']);
        $this->assertSame([12, 14], array_column($parsed['rows'], 'row_number'));
        $this->assertSame('Assigned team member', $parsed['headers'][0]['label']);
        $this->assertSame('assigned_to_name', $mapping['col_0']['field']);
        $this->assertSame('phone', $mapping['col_1']['field']);
        $this->assertSame('email', $mapping['col_2']['field']);
        $this->assertSame('full_name', $mapping['col_3']['field']);
        $this->assertSame('Foysol', $parsed['rows'][0]['values']['col_0']);
        $this->assertSame('Muhammad Yousaf', $parsed['rows'][0]['values']['col_3']);
    }

    public function test_administration_status_labels_are_normalized_to_crm_statuses(): void
    {
        $normalizer = new LeadImportValueNormalizer;
        $headers = [['key' => 'col_0', 'label' => 'Current status', 'index' => 0]];

        $successful = $normalizer->normalize($headers, ['col_0' => 'lead_status'], ['col_0' => 'Succesful']);
        $inProgress = $normalizer->normalize($headers, ['col_0' => 'lead_status'], ['col_0' => 'In progress']);
        $notInterested = $normalizer->normalize($headers, ['col_0' => 'lead_status'], ['col_0' => 'Spam / Not interested']);

        $this->assertSame('won', $successful['fields']['lead_status']);
        $this->assertSame('contacted', $inProgress['fields']['lead_status']);
        $this->assertSame('lost', $notInterested['fields']['lead_status']);
        $this->assertStringNotContainsString('Unknown status', implode(' ', $successful['warnings']));
    }
}
