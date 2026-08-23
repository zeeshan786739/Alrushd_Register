<?php

namespace Tests\Unit\Crm;

use App\Services\Crm\LeadImport\LeadImportFileReader;
use App\Services\Crm\LeadImport\LeadImportHeaderNormalizer;
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
}
