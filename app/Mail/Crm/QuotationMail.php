<?php

namespace App\Mail\Crm;

use App\Models\Crm\Quotation;
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
        $pdf = Pdf::loadView('admin.crm.pdf.quotation', [
            'quotation' => $this->quotation->load(['customer', 'items', 'project']),
        ])->setOptions(['isRemoteEnabled' => true]);

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->quotation->quotation_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
