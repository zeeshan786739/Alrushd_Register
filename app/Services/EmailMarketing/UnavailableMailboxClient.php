<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailMarketing\MailboxSetting;
use App\Services\EmailMarketing\Contracts\MailboxClientInterface;
use RuntimeException;

class UnavailableMailboxClient implements MailboxClientInterface
{
    public function fetchNewMessages(MailboxSetting $settings, ?string $sinceUid = null): array
    {
        throw new RuntimeException(
            'Inbox synchronization is unavailable because webklex/php-imap is not installed.'
        );
    }
}
