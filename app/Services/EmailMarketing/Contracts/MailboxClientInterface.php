<?php

namespace App\Services\EmailMarketing\Contracts;

use App\Models\EmailMarketing\MailboxSetting;

interface MailboxClientInterface
{
    /**
     * @return array<int, array{
     *   message_id:?string,
     *   imap_uid:string,
     *   from_email:?string,
     *   from_name:?string,
     *   to:?string,
     *   cc:?string,
     *   subject:?string,
     *   body_html:?string,
     *   body_text:?string,
     *   received_at:?string,
     *   attachments: array<int, array{name:string,mime:?string,contents:string}>
     * }>
     */
    public function fetchNewMessages(MailboxSetting $settings, ?string $sinceUid = null): array;
}
