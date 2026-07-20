<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;

/** Test double / default when IMAP package is unavailable. */
class FakeMailboxClient implements MailboxClientInterface
{
    /** @param array<int, array<string, mixed>> $messages */
    public function __construct(private array $messages = [])
    {
    }

    public function fetchNewMessages(MailboxSetting $settings, ?string $sinceUid = null): array
    {
        return $this->messages;
    }
}
