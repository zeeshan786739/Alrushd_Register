<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;

/** Test double / default when IMAP package is unavailable. */
class FakeMailboxClient implements MailboxClientInterface
{
    /** @param array<int, array<string, mixed>> $messages */
    public function __construct(private array $messages = [])
    {
    }

    public function fetchNewMessages(MailboxSetting|SenderMailbox $settings, ?string $sinceUid = null): array
    {
        return $this->messages;
    }
}
