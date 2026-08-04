<?php

declare(strict_types=1);

namespace App\Mail;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class MfaCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $code,
        public readonly int $ttlMinutes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.mfa_email_subject'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mfa-code',
            with: [
                'user' => $this->user,
                'code' => $this->code,
                'ttlMinutes' => $this->ttlMinutes,
            ],
        );
    }
}
