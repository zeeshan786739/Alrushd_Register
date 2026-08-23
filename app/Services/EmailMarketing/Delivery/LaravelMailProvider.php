<?php

namespace App\Services\EmailMarketing\Delivery;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Bridge to Laravel Mail (SMTP / log / array) for tests and SMTP fallback.
 */
class LaravelMailProvider implements MailProviderInterface
{
    public function name(): string
    {
        return 'laravel';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function send(OutboundEmail $email): DeliveryResult
    {
        try {
            Mail::html($email->html !== '' ? $email->html : nl2br(e($email->text ?: '')), function ($mail) use ($email) {
                $mail->to($email->to)
                    ->subject($email->subject)
                    ->from($email->fromEmail, $email->fromName ?: $email->fromEmail);

                if ($email->cc !== []) {
                    $mail->cc($email->cc);
                }
                if ($email->bcc !== []) {
                    $mail->bcc($email->bcc);
                }
                if ($email->replyTo) {
                    $mail->replyTo($email->replyTo);
                }

                foreach ($email->attachments as $file) {
                    if (! is_readable($file['path'])) {
                        continue;
                    }
                    $mail->attach($file['path'], [
                        'as' => $file['name'],
                        'mime' => $file['mime'],
                    ]);
                }
            });

            return DeliveryResult::accepted($this->name(), 'laravel-'.Str::uuid()->toString(), 'sent');
        } catch (\Throwable $e) {
            return DeliveryResult::failed($this->name(), Str::limit($e->getMessage(), 500));
        }
    }
}
