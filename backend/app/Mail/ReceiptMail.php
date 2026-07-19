<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $receipt,
        public string $pdfContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "La tua ricevuta - Tavolo {$this->receipt['table_number']}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'receipts.email');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "ricevuta-tavolo-{$this->receipt['table_number']}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
