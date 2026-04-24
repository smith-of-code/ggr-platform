<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailDisplayName;
use App\Models\TourCabinetDocument;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TourCabinetDocumentAnnulledMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailDisplayName;

    public function __construct(
        public User $user,
        public TourCabinetDocument $document,
        public string $adminComment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Документ в личном кабинете туров отклонён',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tour-cabinet-document-annulled',
            with: [
                'userName' => trim(implode(' ', array_filter([
                    (string) ($this->user->last_name ?? ''),
                    (string) ($this->user->first_name ?? ''),
                ], fn ($v) => $v !== ''))),
                'documentTypeLabel' => TourCabinetDocument::typeLabel($this->document->type),
                'adminComment' => $this->adminComment,
                'cabinetUrl' => url(route('tour-cabinet.dashboard', [], false).'#tour-cabinet-documents'),
                'mailFromName' => $this->mailDisplayName(),
            ],
        );
    }
}
