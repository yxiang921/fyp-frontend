<?php

namespace App\Mail;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\MissingPerson;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatrolAlertNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alert $alert,
        public Detection $detection,
        public MissingPerson $missingPerson,
        public Camera $camera,
        public Collection $timeline,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "FRMS Alert Dispatch — {$this->missingPerson->case_number} — {$this->alert->alert_id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patrol-alert',
        );
    }
}
