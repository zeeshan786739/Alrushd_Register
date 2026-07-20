<?php

namespace App\Mail\Crm;

use App\Models\Crm\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice '.$this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.crm.emails.invoice',
            with: ['invoice' => $this->invoice->load(['customer', 'items', 'payments'])],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('admin.crm.pdf.invoice', [
            'invoice' => $this->invoice->load(['customer', 'items', 'project']),
        ])->setOptions(['isRemoteEnabled' => true]);

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->invoice->invoice_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
