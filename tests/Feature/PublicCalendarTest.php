<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_month_shows_empty_state(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('SlotBook')
            ->assertSee('Nothing on the book this month')
            ->assertSee('month-grid', false);
    }

    public function test_calendar_lists_open_hours_for_a_future_day(): void
    {
        $start = now()->copy()->addDays(10)->setTime(10, 0);

        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        $this->get(route('home', ['month' => $start->format('Y-m')]))
            ->assertOk()
            ->assertDontSee('Nothing on the book this month')
            ->assertSee('1 open')
            ->assertSee((string) $start->day);
    }

    public function test_month_query_moves_the_calendar(): void
    {
        $this->get(route('home', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('November 2026')
            ->assertSee('month=2026-10')
            ->assertSee('month=2026-12');
    }

    public function test_invalid_month_falls_back_to_the_current_month(): void
    {
        $this->get(route('home', ['month' => 'not-a-month']))
            ->assertOk()
            ->assertSee(now()->format('F Y'));
    }

    public function test_taken_hour_is_marked_taken_not_open(): void
    {
        $start = now()->copy()->addDays(12)->setTime(14, 0);
        $slot = Slot::factory()->create([
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ]);

        Booking::factory()->create([
            'slot_id' => $slot->id,
            'status' => BookingStatus::Pending,
        ]);

        $this->get(route('home', ['month' => $start->format('Y-m')]))
            ->assertOk()
            ->assertSee('Full')
            ->assertDontSee('>1 open<', false);
    }
}
