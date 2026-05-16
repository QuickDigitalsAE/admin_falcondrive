<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string $mailType = 'client'
    )
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailType === 'admin'
                ? 'New Inquiry Received - ' . ($this->inquiry->name ?: 'Client')
                : 'Thank You For Your Inquiry'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-confirmation',
            with: [
                'isAdminMail' => $this->mailType === 'admin',
            ]
        );
    }
}
