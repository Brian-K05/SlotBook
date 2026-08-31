<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['slot_id', 'guest_name', 'guest_email', 'status', 'paid'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'paid' => 'boolean',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::Cancelled;
    }
}
