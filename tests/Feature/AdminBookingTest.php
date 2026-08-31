<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_week_lists_guest_and_open_hours(): void
    {
        $admin = User::factory()->create();
        $start = now()->startOfWeek()->addDays(2)->setTime(10, 0);

        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        Booking::factory()->create([
            'slot_id' => $slot->id,
            'guest_name' => 'Metro Shop',
            'guest_email' => 'hello@metroshop.test',
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.week', ['week' => $start->toDateString()]))
            ->assertOk()
            ->assertSee('Metro Shop')
            ->assertSee('Pending')
            ->assertSee('Confirm')
            ->assertSee('Cancel')
            ->assertSee('Mark paid');
    }

    public function test_admin_can_confirm_and_mail_is_sent(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Pending,
            'guest_email' => 'luis@example.test',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.confirm', $booking))
            ->assertRedirect(route('admin.week'));

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);

        Mail::assertSent(BookingConfirmed::class, function (BookingConfirmed $mail) use ($booking) {
            return $mail->booking->is($booking)
                && $mail->hasTo('luis@example.test');
        });
    }

    public function test_confirm_is_refused_when_not_pending(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Confirmed,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.confirm', $booking))
            ->assertRedirect(route('admin.week'))
            ->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_admin_can_cancel_and_the_hour_opens_again(): void
    {
        $admin = User::factory()->create();
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Pending,
        ]);
        $slotId = $booking->slot_id;

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.cancel', $booking))
            ->assertRedirect(route('admin.week'));

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertTrue(Slot::query()->find($slotId)->isOpen());
    }

    public function test_admin_can_mark_paid_and_clear_it(): void
    {
        $admin = User::factory()->create();
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Pending,
            'paid' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.paid', $booking))
            ->assertRedirect(route('admin.week'));

        $this->assertTrue($booking->fresh()->paid);

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.paid', $booking))
            ->assertRedirect(route('admin.week'));

        $this->assertFalse($booking->fresh()->paid);
    }

    public function test_cancel_on_an_already_cancelled_hour_does_not_change_it(): void
    {
        $admin = User::factory()->create();
        $booking = Booking::factory()->create([
            'status' => BookingStatus::Cancelled,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.week'))
            ->post(route('admin.bookings.cancel', $booking))
            ->assertRedirect(route('admin.week'));

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }

    public function test_quiet_week_shows_empty_state(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.week', ['week' => now()->addWeeks(6)->toDateString()]))
            ->assertOk()
            ->assertSee('Quiet week');
    }
}
