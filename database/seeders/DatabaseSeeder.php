<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'ana@slotbook.test'],
            [
                'name' => 'Ana Cruz',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $hours = [9, 10, 11, 13, 14, 15, 16];

        for ($day = 0; $day < 7; $day++) {
            $date = $weekStart->copy()->addDays($day);

            foreach ($hours as $hour) {
                $start = $date->copy()->setTime($hour, 0);

                Slot::query()->updateOrCreate(
                    ['starts_at' => $start],
                    ['ends_at' => $start->copy()->addHour()],
                );
            }
        }

        $openSlot = Slot::query()
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->first();

        if (
            Booking::query()->doesntExist()
            && $openSlot
            && $openSlot->activeBooking === null
        ) {
            Booking::query()->create([
                'slot_id' => $openSlot->id,
                'guest_name' => 'Metro Shop',
                'guest_email' => 'hello@metroshop.test',
                'status' => BookingStatus::Pending,
                'paid' => false,
            ]);
        }
    }
}
