<?php

namespace App\Mail\Counselling;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Double opt-in: nothing is sent to an email until its owner clicks this link. */
class ConfirmFollowMail extends Mailable
{
    public function __construct(public string $confirmUrl, public string $targetName, public string $unsubscribeUrl)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm your alerts for ' . $this->targetName);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.counselling.confirm');
    }
}
