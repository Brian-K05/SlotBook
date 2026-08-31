<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['starts_at', 'ends_at'])]
class Slot extends Model
{
    /** @use HasFactory<SlotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->whereIn('status', [
            BookingStatus::Pending,
            BookingStatus::Confirmed,
        ]);
    }

    public function isOpen(): bool
    {
        return $this->starts_at->isFuture() && $this->activeBooking === null;
    }
}
