<?php

namespace Database\Factories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slot>
 */
class SlotFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay()->startOfHour();

        return [
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHour(),
        ];
    }
}
