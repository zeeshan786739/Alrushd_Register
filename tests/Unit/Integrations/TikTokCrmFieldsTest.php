<?php

namespace Tests\Unit\Integrations;

use App\Services\Integrations\TikTok\TikTokCrmFields;
use PHPUnit\Framework\TestCase;

class TikTokCrmFieldsTest extends TestCase
{
    public function test_suggests_only_explicit_tiktok_field_identities(): void
    {
        $this->assertSame('email', TikTokCrmFields::suggest('email'));
        $this->assertSame('phone', TikTokCrmFields::suggest('phone_number'));
        $this->assertSame('first_name', TikTokCrmFields::suggest('first_name'));
        $this->assertSame('last_name', TikTokCrmFields::suggest('last_name'));
        $this->assertNull(TikTokCrmFields::suggest('name'));
        $this->assertNull(TikTokCrmFields::suggest('Which year group?'));
        $this->assertFalse(TikTokCrmFields::isAllowed('organization_id'));
        $this->assertTrue(TikTokCrmFields::isAllowed('selected_school'));
    }
}
