<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('manage crm documents') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $tab = $this->input('active_tab', 'branding');
        $boolKeys = [
            'branding' => [
                'show_logo', 'show_display_name', 'show_address', 'show_email',
                'show_phone', 'show_website', 'show_registration_number', 'show_vat_number',
            ],
            'quotation' => [
                'show_customer_email', 'show_customer_phone', 'show_project', 'show_status',
                'show_issue_date', 'show_valid_until', 'show_subtotal', 'show_discount',
                'show_tax', 'show_terms', 'show_notes',
            ],
            'invoice' => [
                'show_customer_email', 'show_customer_phone', 'show_project', 'show_source_quotation',
                'show_status', 'show_issue_date', 'show_due_date', 'show_subtotal', 'show_discount',
                'show_tax', 'show_total', 'show_amount_paid', 'show_balance_due', 'show_payment_history',
                'show_terms', 'show_bank_name', 'show_account_name', 'show_account_number',
                'show_sort_code', 'show_payment_instructions',
            ],
        ];

        if (! isset($boolKeys[$tab])) {
            return;
        }

        $payload = $this->all();
        foreach ($boolKeys[$tab] as $key) {
            $payload[$tab][$key] = $this->boolean($tab.'.'.$key);
        }

        $this->merge($payload);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'active_tab' => ['nullable', 'in:branding,quotation,invoice'],
            'remove_logo' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'branding.display_name' => ['nullable', 'string', 'max:255'],
            'branding.address' => ['nullable', 'string', 'max:2000'],
            'branding.email' => ['nullable', 'email', 'max:255'],
            'branding.phone' => ['nullable', 'string', 'max:50'],
            'branding.website' => ['nullable', 'string', 'max:255'],
            'branding.registration_number' => ['nullable', 'string', 'max:100'],
            'branding.vat_number' => ['nullable', 'string', 'max:100'],
            'branding.show_logo' => ['boolean'],
            'branding.show_display_name' => ['boolean'],
            'branding.show_address' => ['boolean'],
            'branding.show_email' => ['boolean'],
            'branding.show_phone' => ['boolean'],
            'branding.show_website' => ['boolean'],
            'branding.show_registration_number' => ['boolean'],
            'branding.show_vat_number' => ['boolean'],
            'quotation.heading' => ['nullable', 'string', 'max:100'],
            'quotation.subtitle' => ['nullable', 'string', 'max:255'],
            'quotation.terms_text' => ['nullable', 'string', 'max:5000'],
            'quotation.footer_text' => ['nullable', 'string', 'max:1000'],
            'quotation.show_customer_email' => ['boolean'],
            'quotation.show_customer_phone' => ['boolean'],
            'quotation.show_project' => ['boolean'],
            'quotation.show_status' => ['boolean'],
            'quotation.show_issue_date' => ['boolean'],
            'quotation.show_valid_until' => ['boolean'],
            'quotation.show_subtotal' => ['boolean'],
            'quotation.show_discount' => ['boolean'],
            'quotation.show_tax' => ['boolean'],
            'quotation.show_terms' => ['boolean'],
            'quotation.show_notes' => ['boolean'],
            'invoice.heading' => ['nullable', 'string', 'max:100'],
            'invoice.subtitle' => ['nullable', 'string', 'max:255'],
            'invoice.terms_text' => ['nullable', 'string', 'max:5000'],
            'invoice.footer_text' => ['nullable', 'string', 'max:1000'],
            'invoice.bank_name' => ['nullable', 'string', 'max:255'],
            'invoice.account_name' => ['nullable', 'string', 'max:255'],
            'invoice.account_number' => ['nullable', 'string', 'max:255'],
            'invoice.sort_code' => ['nullable', 'string', 'max:255'],
            'invoice.payment_instructions' => ['nullable', 'string', 'max:5000'],
            'invoice.show_customer_email' => ['boolean'],
            'invoice.show_customer_phone' => ['boolean'],
            'invoice.show_project' => ['boolean'],
            'invoice.show_source_quotation' => ['boolean'],
            'invoice.show_status' => ['boolean'],
            'invoice.show_issue_date' => ['boolean'],
            'invoice.show_due_date' => ['boolean'],
            'invoice.show_subtotal' => ['boolean'],
            'invoice.show_discount' => ['boolean'],
            'invoice.show_tax' => ['boolean'],
            'invoice.show_total' => ['boolean'],
            'invoice.show_amount_paid' => ['boolean'],
            'invoice.show_balance_due' => ['boolean'],
            'invoice.show_payment_history' => ['boolean'],
            'invoice.show_terms' => ['boolean'],
            'invoice.show_bank_name' => ['boolean'],
            'invoice.show_account_name' => ['boolean'],
            'invoice.show_account_number' => ['boolean'],
            'invoice.show_sort_code' => ['boolean'],
            'invoice.show_payment_instructions' => ['boolean'],
        ];
    }
}
