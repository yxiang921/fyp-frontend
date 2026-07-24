<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\MissingPerson;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAlertNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alert $alert,
        public Detection $detection,
        public MissingPerson $missingPerson,
        public Camera $camera,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "FRMS Alert: Potential Match — {$this->missingPerson->case_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-alert',
        );
    }
}
