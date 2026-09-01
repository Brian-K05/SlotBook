<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingActionController extends Controller
{
    public function confirm(Booking $booking): RedirectResponse
    {
        if (! $booking->isPending()) {
            return back()->with('status', 'Only a pending hour can be confirmed.');
        }

        $booking->update(['status' => BookingStatus::Confirmed]);
        $booking->load('slot');

        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmed($booking));
        } catch (\Throwable $e) {
            report($e);
            Log::error('SlotBook confirm mail failed', ['error' => $e->getMessage()]);

            return back()->with('status', 'Confirmed. The note to '.$booking->guest_email.' could not be sent.');
        }

        return back()->with('status', 'Confirmed. A note was sent to '.$booking->guest_email.'.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->isCancelled()) {
            return back()->with('status', 'That hour is already cancelled.');
        }

        $booking->update(['status' => BookingStatus::Cancelled]);

        return back()->with('status', 'Cancelled. That hour is open again.');
    }

    public function togglePaid(Booking $booking): RedirectResponse
    {
        $booking->update(['paid' => ! $booking->paid]);

        $label = $booking->paid ? 'Marked paid.' : 'Paid flag cleared.';

        return back()->with('status', $label);
    }
}
