<?php

namespace App\Support;

use App\Models\Crm\DocumentSetting;
use App\Models\Organization;
use ArPHP\I18N\Arabic;
use Illuminate\Support\Facades\Storage;

/**
 * Central CRM Quotation/Invoice document helpers:
 * organization-scoped settings resolution, visibility, and DomPDF Arabic shaping.
 */
final class CrmDocument
{
    /** @return array<string, mixed> */
    public static function pdfOptions(): array
    {
        return [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'convert_entities' => false,
        ];
    }

    public static function organizationFor(?int $organizationId): ?Organization
    {
        if (! $organizationId) {
            return null;
        }

        return Organization::query()->find($organizationId);
    }

    public static function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2);
    }

    public static function containsArabic(?string $text): bool
    {
        if ($text === null || $text === '') {
            return false;
        }

        return (bool) preg_match('/\p{Arabic}/u', $text);
    }

    public static function textDir(?string $text, bool $forPdf = false): string
    {
        if ($forPdf) {
            // After utf8Glyphs preprocessing, DomPDF expects visual LTR runs.
            return '';
        }

        return self::containsArabic($text) ? 'dir="rtl"' : 'dir="auto"';
    }

    /**
     * Shape a single Arabic string for DomPDF (tests / isolated use).
     * Prefer prepareHtmlForPdf() for full documents.
     */
    public static function pdfText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (! self::containsArabic($text)) {
            return $text;
        }

        static $arabic = null;
        $arabic ??= new Arabic;

        return $arabic->utf8Glyphs($text);
    }

    /**
     * Prepare fully rendered HTML for DomPDF by shaping Arabic runs in place.
     * Does not mutate database values. Do not use on browser preview HTML.
     */
    public static function prepareHtmlForPdf(string $html): string
    {
        if (! self::containsArabic($html)) {
            return $html;
        }

        $arabic = new Arabic;
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 1; $i -= 2) {
            $start = $positions[$i - 1];
            $end = $positions[$i];
            $length = $end - $start;
            if ($length <= 0) {
                continue;
            }

            $segment = substr($html, $start, $length);
            $shaped = $arabic->utf8Glyphs($segment);
            $html = substr_replace($html, $shaped, $start, $length);
        }

        return $html;
    }

    /** @return array{branding: array<string, mixed>, quotation: array<string, mixed>, invoice: array<string, mixed>, logo_path: ?string, organization_id: int} */
    public static function settings(int $organizationId): array
    {
        $row = DocumentSetting::query()
            ->where('organization_id', $organizationId)
            ->first();

        return [
            'organization_id' => $organizationId,
            'logo_path' => $row?->logo_path,
            'branding' => self::mergeDefaults(self::defaultBranding(), $row?->branding ?? []),
            'quotation' => self::mergeDefaults(self::defaultQuotation(), $row?->quotation ?? []),
            'invoice' => self::mergeDefaults(self::defaultInvoice(), $row?->invoice ?? []),
        ];
    }

    /**
     * Resolved view model for quotation documents (preview + PDF).
     *
     * @return array<string, mixed>
     */
    public static function quotationViewData(object $quotation, string $mode = 'preview'): array
    {
        $settings = self::settings((int) $quotation->organization_id);
        $branding = $settings['branding'];
        $options = $settings['quotation'];
        $isPdf = $mode === 'pdf';

        // Keep UTF-8 in templates. DomPDF shaping is applied via prepareHtmlForPdf().
        $text = static fn (?string $value): string => (string) ($value ?? '');

        $logoSrc = null;
        if (! empty($branding['show_logo'])) {
            $logoSrc = self::logoSrcForDocument($settings['logo_path'], (int) $quotation->organization_id);
        }

        $brandLines = [];
        if (! empty($branding['show_address']) && filled($branding['address'] ?? null)) {
            $brandLines[] = (string) $branding['address'];
        }
        if (! empty($branding['show_email']) && filled($branding['email'] ?? null)) {
            $brandLines[] = (string) $branding['email'];
        }
        if (! empty($branding['show_phone']) && filled($branding['phone'] ?? null)) {
            $brandLines[] = (string) $branding['phone'];
        }
        if (! empty($branding['show_website']) && filled($branding['website'] ?? null)) {
            $brandLines[] = (string) $branding['website'];
        }
        if (! empty($branding['show_registration_number']) && filled($branding['registration_number'] ?? null)) {
            $brandLines[] = 'Reg: '.$branding['registration_number'];
        }
        if (! empty($branding['show_vat_number']) && filled($branding['vat_number'] ?? null)) {
            $brandLines[] = 'VAT: '.$branding['vat_number'];
        }

        $displayName = (! empty($branding['show_display_name']) && filled($branding['display_name'] ?? null))
            ? (string) $branding['display_name']
            : null;

        $statusLabel = match ($quotation->status) {
            'sent' => 'Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            'draft' => 'Draft',
            default => ucfirst(str_replace('_', ' ', (string) $quotation->status)),
        };
        $statusClass = match ($quotation->status) {
            'sent' => 'crm-doc-q-status-sent',
            'accepted' => 'crm-doc-q-status-accepted',
            'rejected' => 'crm-doc-q-status-rejected',
            default => 'crm-doc-q-status-draft',
        };
        if ($quotation->converted_invoice_id) {
            $statusLabel = 'Converted';
            $statusClass = 'crm-doc-q-status-accepted';
        }

        $termsText = null;
        if (! empty($options['show_terms'])) {
            $termsText = filled($quotation->terms ?? null)
                ? (string) $quotation->terms
                : (filled($options['terms_text'] ?? null) ? (string) $options['terms_text'] : null);
        }

        $notesText = (! empty($options['show_notes']) && filled($quotation->notes ?? null))
            ? (string) $quotation->notes
            : null;

        return [
            'mode' => $mode,
            'is_pdf' => $isPdf,
            'logo_src' => $logoSrc,
            'display_name' => $displayName,
            'brand_lines' => $brandLines,
            'heading' => filled($options['heading'] ?? null) ? (string) $options['heading'] : 'Quotation',
            'subtitle' => filled($options['subtitle'] ?? null) ? (string) $options['subtitle'] : null,
            'show_status' => (bool) ($options['show_status'] ?? true),
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
            'show_customer_email' => (bool) ($options['show_customer_email'] ?? true),
            'show_customer_phone' => (bool) ($options['show_customer_phone'] ?? true),
            'show_project' => (bool) ($options['show_project'] ?? true),
            'show_issue_date' => (bool) ($options['show_issue_date'] ?? true),
            'show_valid_until' => (bool) ($options['show_valid_until'] ?? true),
            'show_subtotal' => (bool) ($options['show_subtotal'] ?? true),
            'show_discount' => (bool) ($options['show_discount'] ?? true),
            'show_tax' => (bool) ($options['show_tax'] ?? true),
            'terms_text' => $termsText,
            'notes_text' => $notesText,
            'footer_text' => filled($options['footer_text'] ?? null) ? (string) $options['footer_text'] : null,
            'text' => $text,
        ];
    }

    /**
     * Resolved view model for invoice documents (preview + PDF).
     *
     * @return array<string, mixed>
     */
    public static function invoiceViewData(object $invoice, string $mode = 'preview'): array
    {
        $settings = self::settings((int) $invoice->organization_id);
        $branding = $settings['branding'];
        $options = $settings['invoice'];
        $isPdf = $mode === 'pdf';

        // Keep UTF-8 in templates. DomPDF shaping is applied via prepareHtmlForPdf().
        $text = static fn (?string $value): string => (string) ($value ?? '');

        $logoSrc = null;
        if (! empty($branding['show_logo'])) {
            $logoSrc = self::logoSrcForDocument($settings['logo_path'], (int) $invoice->organization_id);
        }

        $brandLines = [];
        if (! empty($branding['show_address']) && filled($branding['address'] ?? null)) {
            $brandLines[] = (string) $branding['address'];
        }
        if (! empty($branding['show_email']) && filled($branding['email'] ?? null)) {
            $brandLines[] = (string) $branding['email'];
        }
        if (! empty($branding['show_phone']) && filled($branding['phone'] ?? null)) {
            $brandLines[] = (string) $branding['phone'];
        }
        if (! empty($branding['show_website']) && filled($branding['website'] ?? null)) {
            $brandLines[] = (string) $branding['website'];
        }
        if (! empty($branding['show_registration_number']) && filled($branding['registration_number'] ?? null)) {
            $brandLines[] = 'Reg: '.$branding['registration_number'];
        }
        if (! empty($branding['show_vat_number']) && filled($branding['vat_number'] ?? null)) {
            $brandLines[] = 'VAT: '.$branding['vat_number'];
        }

        $displayName = (! empty($branding['show_display_name']) && filled($branding['display_name'] ?? null))
            ? (string) $branding['display_name']
            : null;

        $dueState = InvoiceDueState::forInvoice($invoice);
        $isPaid = $invoice->status === 'paid' || (float) $invoice->due_amount <= 0.001;
        $isPartial = $invoice->status === 'partially_paid' || ((float) $invoice->paid_amount > 0 && ! $isPaid);
        $isOverdueDisplay = $dueState->state === InvoiceDueState::OVERDUE;

        $stampClass = 'crm-doc-i-stamp-draft';
        $stampLabel = ucfirst(str_replace('_', ' ', (string) $invoice->status));
        if ($isPaid) {
            $stampClass = '';
            $stampLabel = 'Paid';
        } elseif ($isOverdueDisplay) {
            $stampClass = 'crm-doc-i-stamp-overdue';
            $stampLabel = 'Overdue';
        } elseif ($isPartial) {
            $stampClass = 'crm-doc-i-stamp-partial';
            $stampLabel = 'Partially Paid';
        } elseif ($invoice->status === 'sent') {
            $stampClass = 'crm-doc-i-stamp-sent';
            $stampLabel = 'Sent';
        }

        $termsText = null;
        if (! empty($options['show_terms'])) {
            $termsText = filled($invoice->terms ?? null)
                ? (string) $invoice->terms
                : (filled($options['terms_text'] ?? null) ? (string) $options['terms_text'] : null);
        }

        $paymentInstructions = [];
        $bankFields = [
            'bank_name' => 'Bank',
            'account_name' => 'Account name',
            'account_number' => 'Account / IBAN',
            'sort_code' => 'Sort code / SWIFT',
            'payment_instructions' => 'Instructions',
        ];
        foreach ($bankFields as $key => $label) {
            $showKey = 'show_'.$key;
            if (! empty($options[$showKey]) && filled($options[$key] ?? null)) {
                $paymentInstructions[] = [
                    'label' => $label,
                    'value' => (string) $options[$key],
                ];
            }
        }

        return [
            'mode' => $mode,
            'is_pdf' => $isPdf,
            'logo_src' => $logoSrc,
            'display_name' => $displayName,
            'brand_lines' => $brandLines,
            'heading' => filled($options['heading'] ?? null) ? (string) $options['heading'] : 'Invoice',
            'subtitle' => filled($options['subtitle'] ?? null) ? (string) $options['subtitle'] : null,
            'show_status' => (bool) ($options['show_status'] ?? true),
            'stamp_label' => $stampLabel,
            'stamp_class' => $stampClass,
            'is_paid' => $isPaid,
            'is_overdue_display' => $isOverdueDisplay,
            'show_customer_email' => (bool) ($options['show_customer_email'] ?? true),
            'show_customer_phone' => (bool) ($options['show_customer_phone'] ?? true),
            'show_project' => (bool) ($options['show_project'] ?? true),
            'show_source_quotation' => (bool) ($options['show_source_quotation'] ?? true),
            'show_issue_date' => (bool) ($options['show_issue_date'] ?? true),
            'show_due_date' => (bool) ($options['show_due_date'] ?? true),
            'show_subtotal' => (bool) ($options['show_subtotal'] ?? true),
            'show_discount' => (bool) ($options['show_discount'] ?? true),
            'show_tax' => (bool) ($options['show_tax'] ?? true),
            'show_total' => (bool) ($options['show_total'] ?? true),
            'show_amount_paid' => (bool) ($options['show_amount_paid'] ?? true),
            'show_balance_due' => (bool) ($options['show_balance_due'] ?? true),
            'show_payment_history' => (bool) ($options['show_payment_history'] ?? true),
            'terms_text' => $termsText,
            'footer_text' => filled($options['footer_text'] ?? null) ? (string) $options['footer_text'] : null,
            'payment_instructions' => $paymentInstructions,
            'text' => $text,
        ];
    }

    /** @return array<string, mixed> */
    public static function defaultBranding(): array
    {
        return [
            'display_name' => '',
            'address' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'registration_number' => '',
            'vat_number' => '',
            'show_logo' => false,
            'show_display_name' => false,
            'show_address' => false,
            'show_email' => false,
            'show_phone' => false,
            'show_website' => false,
            'show_registration_number' => false,
            'show_vat_number' => false,
        ];
    }

    /** @return array<string, mixed> */
    public static function defaultQuotation(): array
    {
        return [
            'heading' => 'Quotation',
            'subtitle' => '',
            'show_customer_email' => true,
            'show_customer_phone' => true,
            'show_project' => true,
            'show_status' => true,
            'show_issue_date' => true,
            'show_valid_until' => true,
            'show_subtotal' => true,
            'show_discount' => true,
            'show_tax' => true,
            'show_terms' => true,
            'terms_text' => '',
            'show_notes' => true,
            'footer_text' => '',
        ];
    }

    /** @return array<string, mixed> */
    public static function defaultInvoice(): array
    {
        return [
            'heading' => 'Invoice',
            'subtitle' => '',
            'show_customer_email' => true,
            'show_customer_phone' => true,
            'show_project' => true,
            'show_source_quotation' => true,
            'show_status' => true,
            'show_issue_date' => true,
            'show_due_date' => true,
            'show_subtotal' => true,
            'show_discount' => true,
            'show_tax' => true,
            'show_total' => true,
            'show_amount_paid' => true,
            'show_balance_due' => true,
            'show_payment_history' => true,
            'show_terms' => true,
            'terms_text' => '',
            'footer_text' => '',
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'sort_code' => '',
            'payment_instructions' => '',
            'show_bank_name' => false,
            'show_account_name' => false,
            'show_account_number' => false,
            'show_sort_code' => false,
            'show_payment_instructions' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public static function mergeDefaults(array $defaults, array $stored): array
    {
        $merged = $defaults;
        foreach ($defaults as $key => $default) {
            if (! array_key_exists($key, $stored)) {
                continue;
            }
            $value = $stored[$key];
            if (is_bool($default)) {
                $merged[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } else {
                $merged[$key] = is_string($value) ? $value : (string) ($value ?? '');
            }
        }

        return $merged;
    }

    /**
     * Resolve a DomPDF + browser-safe logo source for document rendering.
     * Uses a data URI so preview/PDF work without a public storage symlink
     * and without exposing the logo via a public URL.
     */
    public static function logoSrcForDocument(?string $path, ?int $organizationId = null): ?string
    {
        return self::logoDataUri($path, $organizationId);
    }

    /**
     * Settings-page thumbnail source (same data-URI approach; not publicly exposed).
     */
    public static function logoForPreview(?string $path, ?int $organizationId = null): ?string
    {
        return self::logoDataUri($path, $organizationId);
    }

    /**
     * DomPDF-compatible logo source.
     */
    public static function logoForPdf(?string $path, ?int $organizationId = null): ?string
    {
        return self::logoDataUri($path, $organizationId);
    }

    public static function logoDataUri(?string $path, ?int $organizationId = null): ?string
    {
        $absolute = self::logoAbsolutePathFromSetting($path, $organizationId);
        if (! $absolute || ! is_readable($absolute)) {
            return null;
        }

        $mime = self::logoMimeType($absolute, $path);
        if (! $mime) {
            return null;
        }

        $binary = @file_get_contents($absolute);
        if ($binary === false || $binary === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    public static function logoAbsolutePathFromSetting(?string $path, ?int $organizationId = null): ?string
    {
        if (! $path) {
            return null;
        }

        // Tenant path guard: only org-scoped document logos.
        if ($organizationId !== null) {
            $prefix = 'crm-documents/'.$organizationId.'/';
            if (! str_starts_with($path, $prefix)) {
                return null;
            }
        } elseif (! preg_match('#^crm-documents/\d+/#', $path)) {
            return null;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return null;
    }

    /**
     * @deprecated Prefer logoForPreview()/logoSrcForDocument() data URIs.
     */
    public static function logoPublicUrlFromSetting(?string $path, ?int $organizationId = null): ?string
    {
        return self::logoDataUri($path, $organizationId);
    }

    private static function logoMimeType(string $absolutePath, ?string $storagePath = null): ?string
    {
        $allowed = [
            'image/png' => true,
            'image/jpeg' => true,
            'image/jpg' => true,
        ];

        $mime = null;
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($absolutePath);
            if (is_string($detected)) {
                $mime = strtolower($detected);
            }
        }

        if (! $mime || ! isset($allowed[$mime])) {
            $ext = strtolower(pathinfo($storagePath ?: $absolutePath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default => null,
            };
        }

        if ($mime === 'image/jpg') {
            $mime = 'image/jpeg';
        }

        return ($mime && isset($allowed[$mime])) ? $mime : null;
    }
}
