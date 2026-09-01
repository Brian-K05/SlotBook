<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $booking = DB::transaction(function () use ($data) {
                $slot = Slot::query()
                    ->whereKey($data['slot_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($slot->starts_at->isPast()) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'That hour has already passed.',
                    ]);
                }

                $taken = Booking::query()
                    ->where('slot_id', $slot->id)
                    ->whereIn('status', [
                        BookingStatus::Pending,
                        BookingStatus::Confirmed,
                    ])
                    ->exists();

                if ($taken) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'That hour is already booked.',
                    ]);
                }

                return Booking::query()->create([
                    'slot_id' => $slot->id,
                    'guest_name' => $data['name'],
                    'guest_email' => $data['email'],
                    'status' => BookingStatus::Pending,
                    'paid' => false,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'slot_id' => 'That hour is already booked.',
            ]);
        }

        $booking->load('slot');

        try {
            Mail::to($booking->guest_email)->send(new BookingReceived($booking));
        } catch (\Throwable $e) {
            report($e);
            Log::error('SlotBook booking mail failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('home', [
                    'month' => $booking->slot->starts_at->format('Y-m'),
                    'day' => $booking->slot->starts_at->toDateString(),
                ])
                ->with('status', 'Hold that hour. The note to '.$booking->guest_email.' could not be sent.');
        }

        return redirect()
            ->route('home', [
                'month' => $booking->slot->starts_at->format('Y-m'),
                'day' => $booking->slot->starts_at->toDateString(),
            ])
            ->with('status', 'Hold that hour. A note was sent to '.$booking->guest_email.'. We will write again when it is confirmed.');
    }
}
