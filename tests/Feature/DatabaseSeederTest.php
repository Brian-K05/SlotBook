<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_admin_week_and_one_dummy_booking(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'ana@slotbook.test')->first();

        $this->assertNotNull($admin);
        $this->assertSame('Ana Cruz', $admin->name);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertSame(49, Slot::query()->count());
        $this->assertSame(1, Booking::query()->count());
    }
}
