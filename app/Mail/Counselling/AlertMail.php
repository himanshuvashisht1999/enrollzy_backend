<?php

namespace App\Mail\Counselling;

use App\Models\Counselling\Alert;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/** One alert, always with the official source and a working unsubscribe link (also sent as List-Unsubscribe). */
class AlertMail extends Mailable
{
    public function __construct(public Alert $alert, public string $unsubscribeUrl, public string $manageUrl)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->alert->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.counselling.alert');
    }

    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<' . $this->unsubscribeUrl . '>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click']);
    }
}
