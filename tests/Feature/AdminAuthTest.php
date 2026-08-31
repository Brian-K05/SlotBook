<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to the week')
            ->assertSee('Enter the week');
    }

    public function test_guests_cannot_open_the_week(): void
    {
        $this->get(route('admin.week'))
            ->assertRedirect(route('login'));
    }

    public function test_guests_cannot_confirm_cancel_or_mark_paid(): void
    {
        $this->post(route('admin.bookings.confirm', 1))->assertRedirect(route('login'));
        $this->post(route('admin.bookings.cancel', 1))->assertRedirect(route('login'));
        $this->post(route('admin.bookings.paid', 1))->assertRedirect(route('login'));
    }

    public function test_admin_can_sign_in_and_see_the_week(): void
    {
        $admin = User::factory()->create([
            'email' => 'ana@slotbook.test',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.week'));

        $this->get(route('admin.week'))
            ->assertOk()
            ->assertSee('Week')
            ->assertSee('week-grid', false);
    }

    public function test_bad_password_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'ana@slotbook.test',
            'password' => 'password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'ana@slotbook.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_signed_in_admin_is_sent_to_the_week_from_login(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('login'))
            ->assertRedirect(route('admin.week'));
    }
}
