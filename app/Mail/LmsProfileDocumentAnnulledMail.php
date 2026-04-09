<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailDisplayName;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfileDocument;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LmsProfileDocumentAnnulledMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailDisplayName;

    public function __construct(
        public User $user,
        public LmsEvent $event,
        public LmsProfileDocument $document,
        public string $adminComment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Документ отклонён — {$this->event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lms-profile-document-annulled',
            with: [
                'userName' => trim("{$this->user->last_name} {$this->user->first_name}"),
                'eventTitle' => $this->event->title,
                'documentTypeLabel' => $this->documentTypeLabel(),
                'adminComment' => $this->adminComment,
                'profileUrl' => url(route('lms.profile.edit', ['event' => $this->event->slug])),
                'mailFromName' => $this->mailDisplayName(),
            ],
        );
    }

    private function documentTypeLabel(): string
    {
        $labels = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление на зачисление',
            LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
            LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие на обработку ПД',
            LmsProfileDocument::TYPE_NAME_CHANGE_CERTIFICATE => 'Свидетельство о смене фамилии',
        ];

        return $labels[$this->document->type] ?? $this->document->type;
    }
}
