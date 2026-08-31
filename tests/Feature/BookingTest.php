<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_book_an_open_slot(): void
    {
        Mail::fake();

        $start = now()->addDay()->setTime(10, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $this->post(route('bookings.store'), [
            'slot_id' => $slot->id,
            'name' => 'Luis Santos',
            'email' => 'luis@example.test',
        ])
            ->assertRedirect(route('home', [
                'month' => $start->format('Y-m'),
                'day' => $start->toDateString(),
            ]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('bookings', [
            'slot_id' => $slot->id,
            'guest_name' => 'Luis Santos',
            'guest_email' => 'luis@example.test',
            'status' => BookingStatus::Pending->value,
            'paid' => 0,
        ]);

        Mail::assertNothingSent();
    }

    public function test_the_same_slot_cannot_be_double_booked(): void
    {
        $start = now()->addDay()->setTime(14, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        Booking::factory()->create([
            'slot_id' => $slot->id,
            'status' => BookingStatus::Pending,
        ]);

        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => $slot->id,
                'name' => 'Metro Shop',
                'email' => 'hello@metroshop.test',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('slot_id');

        $this->assertSame(1, Booking::query()->where('slot_id', $slot->id)->count());
    }

    public function test_confirmed_slot_cannot_be_booked_again(): void
    {
        $start = now()->addDays(2)->setTime(11, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        Booking::factory()->create([
            'slot_id' => $slot->id,
            'status' => BookingStatus::Confirmed,
        ]);

        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => $slot->id,
                'name' => 'Metro Shop',
                'email' => 'hello@metroshop.test',
            ])
            ->assertSessionHasErrors('slot_id');
    }

    public function test_cancelled_slot_can_be_booked_again(): void
    {
        $start = now()->addDays(2)->setTime(9, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        Booking::factory()->create([
            'slot_id' => $slot->id,
            'status' => BookingStatus::Cancelled,
        ]);

        $this->post(route('bookings.store'), [
            'slot_id' => $slot->id,
            'name' => 'Metro Shop',
            'email' => 'hello@metroshop.test',
        ])->assertRedirect();

        $this->assertSame(2, Booking::query()->where('slot_id', $slot->id)->count());
        $this->assertTrue(
            Booking::query()
                ->where('slot_id', $slot->id)
                ->where('status', BookingStatus::Pending)
                ->exists()
        );
    }

    public function test_past_slot_cannot_be_booked(): void
    {
        $start = now()->subDay()->setTime(10, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => $slot->id,
                'name' => 'Luis Santos',
                'email' => 'luis@example.test',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('slot_id');

        $this->assertDatabaseMissing('bookings', ['slot_id' => $slot->id]);
    }

    public function test_booking_requires_name_and_email(): void
    {
        $start = now()->addDay()->setTime(11, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => $slot->id,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_booking_rejects_a_bad_email(): void
    {
        $start = now()->addDay()->setTime(13, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => $slot->id,
                'name' => 'Luis Santos',
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_booking_requires_a_real_slot(): void
    {
        $this->from(route('home'))
            ->post(route('bookings.store'), [
                'slot_id' => 999,
                'name' => 'Luis Santos',
                'email' => 'luis@example.test',
            ])
            ->assertSessionHasErrors('slot_id');
    }
}
