<?php

declare(strict_types=1);

namespace App\Mail;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TrialVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme seu email e ative seu trial de 14 dias',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-verification',
        );
    }
}
