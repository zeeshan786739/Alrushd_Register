<?php

namespace App\Enums\EmailMarketing;

enum MessageFolder: string
{
    case Inbox = 'inbox';
    case Sent = 'sent';
    case Draft = 'draft';
}
