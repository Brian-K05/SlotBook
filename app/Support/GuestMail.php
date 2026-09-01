<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class GuestMail
{
    public static function send(string $to, Mailable $mail): void
    {
        if (self::apiReady()) {
            self::sendViaBrevoApi($to, $mail);

            return;
        }

        if (app()->environment('production')) {
            throw new RuntimeException(
                'Railway Hobby blocks SMTP. Add BREVO_KEY from Brevo → SMTP & API → API keys, and keep MAIL_FROM_ADDRESS as the Gmail verified in Brevo.'
            );
        }

        Mail::to($to)->send($mail);

        Log::info('SlotBook mail sent', [
            'mailer' => config('mail.default'),
            'to' => $to,
        ]);
    }

    public static function apiReady(): bool
    {
        return self::apiKey() !== null && self::fromIsLive();
    }

    private static function sendViaBrevoApi(string $to, Mailable $mail): void
    {
        $from = (string) config('mail.from.address');
        $name = (string) config('mail.from.name', 'SlotBook');
        $subject = $mail->envelope()->subject ?? 'SlotBook';

        $response = Http::withHeaders([
            'api-key' => self::apiKey(),
            'accept' => 'application/json',
        ])
            ->timeout(12)
            ->asJson()
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $name !== '' ? $name : 'SlotBook',
                    'email' => $from,
                ],
                'to' => [
                    ['email' => $to],
                ],
                'subject' => $subject,
                'htmlContent' => $mail->render(),
            ]);

        if ($response->failed()) {
            Log::error('SlotBook Brevo API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);

            throw new RuntimeException('Brevo API '.$response->status().': '.$response->body());
        }

        Log::info('SlotBook mail sent', [
            'mailer' => 'brevo-api',
            'from' => $from,
            'to' => $to,
            'id' => $response->json('messageId'),
        ]);
    }

    private static function apiKey(): ?string
    {
        $key = config('services.brevo.key');

        if (self::filled($key)) {
            return $key;
        }

        $password = config('mail.mailers.smtp.password');

        if (is_string($password) && str_starts_with($password, 'xkeysib-')) {
            return $password;
        }

        return null;
    }

    private static function fromIsLive(): bool
    {
        $from = config('mail.from.address');

        if (! self::filled($from)) {
            return false;
        }

        $from = strtolower($from);

        return ! str_contains($from, 'slotbook.test')
            && ! str_contains($from, 'resend.dev')
            && ! str_contains($from, 'example.com');
    }

    private static function filled(mixed $value): bool
    {
        return is_string($value) && $value !== '' && strtolower($value) !== 'null';
    }
}
