<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class GuestMail
{
    public static function send(string $to, Mailable $mail): void
    {
        if (app()->environment('production') && ! self::smtpReady()) {
            throw new RuntimeException(
                'SMTP is not configured. On the SlotBook web service set MAIL_MAILER=smtp, MAIL_HOST=smtp-relay.brevo.com, MAIL_PORT=587, MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS to the Gmail verified in Brevo. Do not set MAIL_SCHEME.'
            );
        }

        if (self::smtpReady()) {
            Mail::mailer('smtp')->to($to)->send($mail);
        } else {
            Mail::to($to)->send($mail);
        }

        Log::info('SlotBook mail sent', [
            'mailer' => self::smtpReady() ? 'smtp' : config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'from' => config('mail.from.address'),
            'to' => $to,
        ]);
    }

    public static function smtpReady(): bool
    {
        $user = config('mail.mailers.smtp.username');
        $pass = config('mail.mailers.smtp.password');
        $from = config('mail.from.address');
        $host = config('mail.mailers.smtp.host');

        return self::filled($user)
            && self::filled($pass)
            && self::filled($from)
            && ! str_contains(strtolower((string) $from), 'slotbook.test')
            && ! str_contains(strtolower((string) $from), 'resend.dev')
            && ! str_contains(strtolower((string) $from), 'example.com')
            && self::filled($host)
            && ! in_array($host, ['127.0.0.1', 'localhost'], true);
    }

    private static function filled(mixed $value): bool
    {
        return is_string($value) && $value !== '' && strtolower($value) !== 'null';
    }
}
