<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $siteName = site('site_name') ?? config('app.name', 'Bina Site Kit');
        $subject = $this->inquiry->subject ?: 'Message from '.$this->inquiry->name;

        return new Envelope(
            subject: "[{$siteName}] {$subject}",
            replyTo: $this->inquiry->email ? [$this->inquiry->email] : null,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-notification',
        );
    }
}
