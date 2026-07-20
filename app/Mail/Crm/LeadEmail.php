<?php

namespace App\Mail\Crm;

use App\Models\Crm\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public string $subjectLine, public string $body)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.crm.emails.lead',
            with: [
                'lead' => $this->lead,
                'body' => $this->body,
            ],
        );
    }
}
