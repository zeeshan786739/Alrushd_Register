<?php

namespace App\Services\Crm;

use App\Enums\EmailMarketing\DeliveryStatus;
use App\Models\Crm\Invoice;
use App\Models\Crm\Lead;
use App\Models\Crm\Quotation;
use App\Models\EmailMarketing\MailboxSetting;
use App\Models\EmailMarketing\Message;
use App\Services\EmailMarketing\Delivery\DeliveryResult;
use App\Services\EmailMarketing\Delivery\EmailDeliveryService;
use App\Services\EmailMarketing\Delivery\OutboundEmail;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\MailConfigResolver;
use App\Support\CrmDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Routes CRM transactional email through EmailDeliveryService (SendGrid/Laravel bridge).
 * Preserves existing templates/PDF generation; only the delivery transport changes.
 */
class CrmTransactionalMailService
{
    public function __construct(
        private EmailDeliveryService $delivery,
        private MailConfigResolver $mailConfig,
        private HtmlSanitizer $sanitizer,
    ) {
    }

    public function sendQuotation(Quotation $quotation, ?int $sentBy = null): DeliveryResult
    {
        $quotation->loadMissing(['customer', 'items', 'project']);
        $to = $quotation->customer?->email;
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return DeliveryResult::failed('none', 'Customer email is missing.');
        }

        $html = view('admin.crm.emails.quotation', ['quotation' => $quotation])->render();
        $subject = 'Quotation '.$quotation->quotation_number;
        $pdfPath = $this->writeTempPdf(
            $this->quotationPdfBinary($quotation),
            $quotation->quotation_number.'.pdf'
        );

        try {
            return $this->dispatch(
                organizationId: (int) $quotation->organization_id,
                to: [$to],
                subject: $subject,
                html: $html,
                attachments: [[
                    'path' => $pdfPath,
                    'name' => $quotation->quotation_number.'.pdf',
                    'mime' => 'application/pdf',
                ]],
                leadId: null,
                customerId: $quotation->customer_id ? (int) $quotation->customer_id : null,
                quotationId: (int) $quotation->id,
                invoiceId: null,
                sentBy: $sentBy,
                category: 'transactional',
            );
        } finally {
            @unlink($pdfPath);
        }
    }

    public function sendInvoice(Invoice $invoice, ?int $sentBy = null): DeliveryResult
    {
        $invoice->loadMissing(['customer', 'items', 'project', 'quotation', 'payments']);
        $to = $invoice->customer?->email;
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return DeliveryResult::failed('none', 'Customer email is missing.');
        }

        $html = view('admin.crm.emails.invoice', ['invoice' => $invoice])->render();
        $subject = 'Invoice '.$invoice->invoice_number;
        $pdfPath = $this->writeTempPdf(
            $this->invoicePdfBinary($invoice),
            $invoice->invoice_number.'.pdf'
        );

        try {
            return $this->dispatch(
                organizationId: (int) $invoice->organization_id,
                to: [$to],
                subject: $subject,
                html: $html,
                attachments: [[
                    'path' => $pdfPath,
                    'name' => $invoice->invoice_number.'.pdf',
                    'mime' => 'application/pdf',
                ]],
                leadId: null,
                customerId: $invoice->customer_id ? (int) $invoice->customer_id : null,
                quotationId: $invoice->quotation_id ? (int) $invoice->quotation_id : null,
                invoiceId: (int) $invoice->id,
                sentBy: $sentBy,
                category: 'transactional',
            );
        } finally {
            @unlink($pdfPath);
        }
    }

    public function sendLeadEmail(Lead $lead, string $subject, string $body, ?int $sentBy = null): DeliveryResult
    {
        if (! $lead->email || ! filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            return DeliveryResult::failed('none', 'Lead email is missing.');
        }

        $html = view('admin.crm.emails.lead', [
            'lead' => $lead,
            'body' => $body,
        ])->render();

        return $this->dispatch(
            organizationId: (int) $lead->organization_id,
            to: [strtolower($lead->email)],
            subject: $subject,
            html: $html,
            attachments: [],
            leadId: (int) $lead->id,
            customerId: $lead->customer_id ? (int) $lead->customer_id : null,
            quotationId: null,
            invoiceId: null,
            sentBy: $sentBy,
            category: 'transactional',
        );
    }

    /**
     * @param  list<string>  $to
     * @param  list<array{path:string,name:string,mime:?string}>  $attachments
     */
    private function dispatch(
        int $organizationId,
        array $to,
        string $subject,
        string $html,
        array $attachments,
        ?int $leadId,
        ?int $customerId,
        ?int $quotationId,
        ?int $invoiceId,
        ?int $sentBy,
        string $category,
    ): DeliveryResult {
        $settings = $this->resolveSettings($organizationId);
        $html = $this->sanitizer->sanitize($html);
        $text = $this->sanitizer->toPlainText($html);
        $correlationUuid = (string) Str::uuid();

        $message = Message::create([
            'organization_id' => $organizationId,
            'folder' => 'sent',
            'direction' => 'outbound',
            'message_id' => 'crm-'.Str::uuid(),
            'correlation_uuid' => $correlationUuid,
            'thread_id' => $correlationUuid,
            'from_email' => $settings->from_email,
            'from_name' => $settings->from_name,
            'to' => implode(', ', $to),
            'subject' => $subject,
            'body_html' => $html,
            'body_text' => $text,
            'delivery_status' => DeliveryStatus::Sending->value,
            'provider_status' => 'pending',
            'lead_id' => $leadId,
            'customer_id' => $customerId,
            'quotation_id' => $quotationId,
            'invoice_id' => $invoiceId,
            'created_by' => $sentBy,
        ]);

        $result = $this->delivery->send(new OutboundEmail(
            fromEmail: (string) $settings->from_email,
            fromName: $settings->from_name,
            to: $to,
            subject: $subject,
            html: $html !== '' ? $html : nl2br(e($text)),
            text: $text,
            replyTo: $this->resolveReplyTo($settings, $correlationUuid),
            attachments: $attachments,
            customArgs: [
                'correlation_uuid' => $correlationUuid,
            ],
            category: $category,
            trackOpens: (bool) ($settings->open_tracking ?? true),
            trackClicks: (bool) ($settings->click_tracking ?? false),
        ), $settings);

        if ($result->accepted) {
            $message->update([
                'delivery_status' => DeliveryStatus::Sent->value,
                'sent_at' => now(),
                'delivery_error' => null,
                'provider' => $result->provider,
                'provider_message_id' => $result->providerMessageId,
                'provider_status' => $result->providerStatus ?: 'processed',
            ]);
        } else {
            $message->update([
                'delivery_status' => DeliveryStatus::Failed->value,
                'delivery_error' => Str::limit($result->error ?: 'Send failed', 500),
                'provider' => $result->provider,
                'provider_status' => 'failed',
            ]);
        }

        return $result;
    }

    private function resolveSettings(int $organizationId): MailboxSetting
    {
        $settings = $this->mailConfig->forOrganization($organizationId);

        if ($settings && filled($settings->from_email) && $settings->is_enabled
            && ($this->mailConfig->sendGridConfigured() || $settings->isSmtpConfigured() || app()->environment('testing'))) {
            return $settings;
        }

        $fallback = $settings ? $settings->replicate() : new MailboxSetting;
        $fallback->organization_id = $organizationId;
        $fallback->is_enabled = true;
        $fallback->from_email = $fallback->from_email ?: config('mail.from.address');
        $fallback->from_name = $fallback->from_name ?: config('mail.from.name');
        $fallback->reply_to = $fallback->reply_to ?: null;
        $fallback->open_tracking = $fallback->open_tracking ?? true;
        $fallback->click_tracking = $fallback->click_tracking ?? false;
        $fallback->tracking_enabled = $fallback->tracking_enabled ?? true;

        if (! filled($fallback->from_email)) {
            throw new \RuntimeException('No sender email is configured for this organization.');
        }

        return $fallback;
    }

    private function resolveReplyTo(MailboxSetting $settings, string $threadId): ?string
    {
        $domain = $settings->inbound_domain ?: config('sendgrid.inbound_domain');
        if ($settings->inbound_enabled && filled($domain)) {
            return 'reply+'.$threadId.'@'.$domain;
        }

        return $settings->reply_to ?: null;
    }

    private function quotationPdfBinary(Quotation $quotation): string
    {
        $organization = CrmDocument::organizationFor($quotation->organization_id);
        $doc = CrmDocument::quotationViewData($quotation, 'pdf');
        $html = CrmDocument::prepareHtmlForPdf(
            view('admin.crm.pdf.quotation', compact('quotation', 'organization', 'doc'))->render()
        );

        return Pdf::loadHTML($html)->setPaper('a4')->setOptions(CrmDocument::pdfOptions())->output();
    }

    private function invoicePdfBinary(Invoice $invoice): string
    {
        $organization = CrmDocument::organizationFor($invoice->organization_id);
        $doc = CrmDocument::invoiceViewData($invoice, 'pdf');
        $html = CrmDocument::prepareHtmlForPdf(
            view('admin.crm.pdf.invoice', compact('invoice', 'organization', 'doc'))->render()
        );

        return Pdf::loadHTML($html)->setPaper('a4')->setOptions(CrmDocument::pdfOptions())->output();
    }

    private function writeTempPdf(string $binary, string $filename): string
    {
        $path = storage_path('app/tmp/crm-mail-'.Str::uuid().'-'.$filename);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $binary);

        return $path;
    }
}
