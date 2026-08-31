<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    public function test_pending_and_confirmed_hold_the_hour(): void
    {
        $this->assertTrue(BookingStatus::Pending->isActive());
        $this->assertTrue(BookingStatus::Confirmed->isActive());
        $this->assertFalse(BookingStatus::Cancelled->isActive());
    }

    public function test_labels_are_readable(): void
    {
        $this->assertSame('Pending', BookingStatus::Pending->label());
        $this->assertSame('Confirmed', BookingStatus::Confirmed->label());
        $this->assertSame('Cancelled', BookingStatus::Cancelled->label());
    }
}
