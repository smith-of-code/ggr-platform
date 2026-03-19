<?php

namespace App\Mail;

use App\Models\Lms\LmsEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public LmsEvent $event,
        public string $activateUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Приглашение на платформу — {$this->event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'userName' => trim("{$this->user->last_name} {$this->user->first_name} {$this->user->patronymic}"),
                'eventTitle' => $this->event->title,
                'activateUrl' => $this->activateUrl,
            ],
        );
    }
}
