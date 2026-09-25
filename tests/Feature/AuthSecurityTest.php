<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_request_sends_a_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_unverified_user_can_verify_email_with_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_last_admin_cannot_be_deleted_or_demoted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->delete(route('users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staff',
        ])->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_staff_cannot_manage_catalog_or_stock_adjustments(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('products.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('stock-adjustments.index'))->assertForbidden();
    }

    public function test_idle_session_is_expired(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['last_activity_at' => now()->subMinutes(31)])
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}