<?php

namespace Tests\Unit\Integrations;

use App\Services\Integrations\TikTok\TikTokLeadFieldMapper;
use PHPUnit\Framework\TestCase;

class TikTokLeadFieldMapperTest extends TestCase
{
    public function test_maps_approved_crm_fields_and_keeps_custom_answers(): void
    {
        $mapper = new TikTokLeadFieldMapper;
        $fields = $mapper->normalizeFieldData([
            ['field' => 'email', 'value' => 'ada@example.com'],
            ['field' => 'phone_number', 'value' => '07123456789'],
            ['field' => 'name', 'value' => 'Ada Lovelace'],
            ['field' => 'Which year group?', 'value' => 'Year 7'],
        ]);

        $attributes = $mapper->mapToLeadAttributes($fields, [
            'email' => 'email',
            'phone_number' => 'phone',
            'name' => 'organization_id',
            'unsupported' => 'first_name',
        ]);

        $this->assertSame('ada@example.com', $attributes['email']);
        $this->assertSame('07123456789', $attributes['phone']);
        $this->assertSame('Ada', $attributes['first_name']);
        $this->assertSame('Lovelace', $attributes['last_name']);
        $this->assertArrayNotHasKey('organization_id', $attributes);
        $this->assertStringContainsString('Which year group?: Year 7', $attributes['lead_description']);
        $this->assertStringContainsString('name: Ada Lovelace', $attributes['lead_description']);
    }
}
