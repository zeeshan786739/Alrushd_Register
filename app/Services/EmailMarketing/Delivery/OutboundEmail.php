<?php

namespace App\Services\EmailMarketing\Delivery;

final class OutboundEmail
{
    /**
     * @param  list<string>  $to
     * @param  list<string>  $cc
     * @param  list<string>  $bcc
     * @param  list<array{path:string,name:string,mime:?string}>  $attachments
     * @param  array<string, string>  $customArgs Opaque correlation only — never PII / org ids.
     */
    public function __construct(
        public readonly string $fromEmail,
        public readonly ?string $fromName,
        public readonly array $to,
        public readonly string $subject,
        public readonly string $html,
        public readonly ?string $text = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly ?string $replyTo = null,
        public readonly array $attachments = [],
        public readonly array $customArgs = [],
        public readonly string $category = 'transactional',
        public readonly bool $trackOpens = false,
        public readonly bool $trackClicks = false,
        public readonly ?int $asmGroupId = null,
    ) {
    }
}
