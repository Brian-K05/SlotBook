<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slot_id' => Slot::factory(),
            'guest_name' => 'Ana Cruz',
            'guest_email' => 'ana.cruz@example.test',
            'status' => BookingStatus::Pending,
            'paid' => false,
        ];
    }
}
