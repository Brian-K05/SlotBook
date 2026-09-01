<?php

namespace Tests\Unit;

use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Support\GuestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class GuestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_refuses_to_send_when_smtp_is_not_ready(): void
    {
        Mail::fake();

        $this->app['env'] = 'production';

        $booking = Booking::factory()->create();

        try {
            GuestMail::send($booking->guest_email, new BookingReceived($booking));
            $this->fail('GuestMail should refuse production send without SMTP.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('SMTP is not configured', $e->getMessage());
        }

        Mail::assertNothingSent();
    }
}
