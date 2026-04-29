<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailDisplayName;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TourCabinetContestCompletionMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailDisplayName;

    public function __construct(
        public User $user,
        public string $subjectText,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        $subject = trim($this->subjectText);
        if ($subject === '') {
            $subject = 'Конкурс пройден — ожидайте обратную связь';
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tour-cabinet-contest-completion',
            with: [
                'userName' => trim(implode(' ', array_filter([
                    (string) ($this->user->last_name ?? ''),
                    (string) ($this->user->first_name ?? ''),
                ], fn ($v) => $v !== ''))),
                'body' => $this->body,
                'cabinetUrl' => url(route('tour-cabinet.dashboard', [], false)),
                'mailFromName' => $this->mailDisplayName(),
            ],
        );
    }
}
