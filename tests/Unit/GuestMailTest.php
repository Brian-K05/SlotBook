<?php

namespace Tests\Unit;

use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Support\GuestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class GuestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_refuses_to_send_without_a_brevo_api_key(): void
    {
        Mail::fake();

        $this->app['env'] = 'production';

        $booking = Booking::factory()->create();

        try {
            GuestMail::send($booking->guest_email, new BookingReceived($booking));
            $this->fail('GuestMail should refuse production send without a Brevo API key.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('BREVO_KEY', $e->getMessage());
        }

        Mail::assertNothingSent();
    }

    public function test_sends_through_the_brevo_https_api(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'abc-123'], 201),
        ]);

        config([
            'services.brevo.key' => 'xkeysib-test',
            'mail.from.address' => 'ana@gmail.com',
            'mail.from.name' => 'SlotBook',
        ]);

        $booking = Booking::factory()->create([
            'guest_email' => 'luis@example.test',
            'guest_name' => 'Luis Santos',
        ]);
        $booking->load('slot');

        GuestMail::send($booking->guest_email, new BookingReceived($booking));

        Mail::assertNothingSent();
        Http::assertSent(function ($request) use ($booking): bool {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'xkeysib-test')
                && $request['sender']['email'] === 'ana@gmail.com'
                && $request['to'][0]['email'] === $booking->guest_email
                && str_contains((string) $request['htmlContent'], 'Luis Santos');
        });
    }
}
