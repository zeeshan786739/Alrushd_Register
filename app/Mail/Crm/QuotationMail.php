<?php

namespace App\Mail\Crm;

use App\Models\Crm\Quotation;
use App\Support\CrmDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quotation '.$this->quotation->quotation_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.crm.emails.quotation',
            with: ['quotation' => $this->quotation->load(['customer', 'items'])],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $quotation = $this->quotation->load(['customer', 'items', 'project']);
        $organization = CrmDocument::organizationFor($quotation->organization_id);
        $doc = CrmDocument::quotationViewData($quotation, 'pdf');
        $html = CrmDocument::prepareHtmlForPdf(
            view('admin.crm.pdf.quotation', compact('quotation', 'organization', 'doc'))->render()
        );

        $pdf = Pdf::loadHTML($html)->setPaper('a4')->setOptions(CrmDocument::pdfOptions());

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->quotation->quotation_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
