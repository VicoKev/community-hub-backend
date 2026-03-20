<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfilRejete extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $statut,
        public readonly ?string $motif,
    ) {}

    public function envelope(): Envelope
    {
        $sujet = $this->statut === 'suspendu'
            ? 'Votre compte a été suspendu — Community Hub'
            : 'Votre dossier a été rejeté — Community Hub';

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profile.profil-rejete',
        );
    }
}
