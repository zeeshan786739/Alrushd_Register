<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\SenderMailbox;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use RuntimeException;

class UnavailableMailboxClient implements MailboxClientInterface
{
    public function fetchNewMessages(MailboxSetting|SenderMailbox $settings, ?string $sinceUid = null): array
    {
        throw new RuntimeException(
            'Inbox synchronization is unavailable because webklex/php-imap is not installed.'
        );
    }
}
