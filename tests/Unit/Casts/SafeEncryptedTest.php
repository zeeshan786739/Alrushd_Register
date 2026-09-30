<?php

namespace Tests\Unit\Casts;

use App\Casts\SafeEncrypted;
use App\Models\EmailMarketing\MailboxSetting;
use Illuminate\Encryption\Encrypter;
use Tests\TestCase;

class SafeEncryptedTest extends TestCase
{
    public function test_round_trips_plaintext(): void
    {
        $cast = new SafeEncrypted;
        $model = new MailboxSetting;
        $stored = $cast->set($model, 'sendgrid_api_key', 'SG.secret-key', []);

        $this->assertNotSame('SG.secret-key', $stored);
        $this->assertSame('SG.secret-key', $cast->get($model, 'sendgrid_api_key', $stored, []));
    }

    public function test_returns_null_when_ciphertext_was_encrypted_with_another_key(): void
    {
        $foreign = new Encrypter(random_bytes(32), config('app.cipher'));
        $ciphertext = $foreign->encrypt('SG.foreign-key', false);

        $value = (new SafeEncrypted)->get(
            new MailboxSetting,
            'sendgrid_api_key',
            $ciphertext,
            []
        );

        $this->assertNull($value);
    }

    public function test_treats_empty_values_as_null(): void
    {
        $cast = new SafeEncrypted;
        $model = new MailboxSetting;

        $this->assertNull($cast->get($model, 'sendgrid_api_key', null, []));
        $this->assertNull($cast->get($model, 'sendgrid_api_key', '', []));
        $this->assertNull($cast->set($model, 'sendgrid_api_key', null, []));
        $this->assertNull($cast->set($model, 'sendgrid_api_key', '', []));
    }
}
