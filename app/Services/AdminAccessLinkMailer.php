<?php

namespace App\Services;

use App\Models\Admin;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\MailConfigResolver;
use RuntimeException;

class AdminAccessLinkMailer
{
    public function __construct(
        private MailConfigResolver $mailConfig,
        private EmailDeliveryService $delivery,
    ) {}

    public function sendInvitation(Admin $admin, string $url, string $inviterName): void
    {
        $this->send(
            $admin,
            $url,
            "You're invited to join",
            'Set up your account',
            e($inviterName).' invited you to join the team. Create a secure password to activate your account.',
            'team-invitation',
        );
    }

    public function sendPasswordReset(Admin $admin, string $url): void
    {
        $this->send(
            $admin,
            $url,
            'Reset your password',
            'Reset password',
            'We received a request to reset your admin password. This secure link expires in 60 minutes.',
            'password-reset',
        );
    }

    private function send(Admin $admin, string $url, string $subjectPrefix, string $button, string $message, string $category): void
    {
        if (! $admin->organization_id) {
            throw new RuntimeException('This account does not have an organization email configuration.');
        }

        $settings = $this->mailConfig->resolveOrFail((int) $admin->organization_id);
        $school = $admin->organization?->name ?: ($settings->from_name ?: config('app.name'));
        $safeName = e($admin->name);
        $safeSchool = e($school);
        $safeUrl = e($url);

        $result = $this->delivery->send(new OutboundEmail(
            fromEmail: $settings->from_email,
            fromName: $settings->from_name ?: $school,
            to: [$admin->email],
            subject: $subjectPrefix.' '.$school,
            html: <<<HTML
                <div style="font-family:Arial,sans-serif;max-width:620px;margin:0 auto;color:#1e293b;line-height:1.65">
                    <div style="border-top:5px solid #c5a86d;padding:28px;border-radius:12px;background:#fff;box-shadow:0 10px 28px rgba(15,39,74,.08)">
                        <h1 style="color:#0f274a;font-size:26px;margin:0 0 18px">{$subjectPrefix}</h1>
                        <p>Hello {$safeName},</p>
                        <p>{$message}</p>
                        <p style="margin:28px 0"><a href="{$safeUrl}" style="display:inline-block;background:#0f274a;color:#fff;padding:13px 24px;border-radius:8px;text-decoration:none;font-weight:700">{$button}</a></p>
                        <p style="font-size:13px;color:#64748b">This link is private and can only be used once. If you did not expect this email, you can safely ignore it.</p>
                        <p style="font-size:13px;color:#64748b">{$safeSchool}</p>
                    </div>
                </div>
                HTML,
            text: "Hello {$admin->name}. {$message} {$button}: {$url}",
            replyTo: $settings->reply_to ?: $settings->from_email,
            category: $category,
        ), $settings);

        if (! $result->accepted) {
            throw new RuntimeException($result->error ?: 'The email provider did not accept the message.');
        }
    }
}
