<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLoginSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('admin-security-test');
        parent::tearDown();
    }

    private function createAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $user->forceFill([
            'is_admin' => true,
            'is_active' => true,
        ])->save();

        return $user->fresh();
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        $admin = $this->createAdmin([
            'email' => 'inactive-admin@example.com',
            'password' => 'StrongPassword!482',
        ]);

        $admin->forceFill(['is_active' => false])->save();

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'StrongPassword!482',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_ordinary_user_cannot_log_in_to_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'ordinary-user@example.com',
            'password' => 'StrongPassword!482',
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'StrongPassword!482',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_five_failed_attempts_trigger_login_throttling(): void
    {
        $email = 'missing-admin@example.com';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.submit'), [
                'email' => $email,
                'password' => 'WrongPassword!482',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.submit'), [
            'email' => $email,
            'password' => 'WrongPassword!482',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Terlalu banyak percobaan login',
            session('errors')->first('email')
        );
    }

    public function test_successful_login_clears_failed_attempt_counter(): void
    {
        $admin = $this->createAdmin([
            'email' => 'valid-admin@example.com',
            'password' => 'StrongPassword!482',
        ]);

        $ip = '127.0.0.1';
        $key = 'valid-admin@example.com|' . $ip;

        // Match the controller's normalized limiter key.
        $key = \Illuminate\Support\Str::transliterate(
            \Illuminate\Support\Str::lower($admin->email) . '|' . $ip
        );

        RateLimiter::hit($key, 60);
        RateLimiter::hit($key, 60);

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'StrongPassword!482',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertSame(0, RateLimiter::attempts($key));
    }

    public function test_authenticated_ordinary_user_is_denied_admin_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }


    public function test_successful_login_regenerates_the_session_id(): void
    {
        $admin = $this->createAdmin([
            'email' => 'session-admin@example.com',
            'password' => 'StrongPassword!482',
        ]);

        $this->startSession();

        $oldSessionId = session()->getId();

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'StrongPassword!482',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_logout_clears_authentication_and_rotates_csrf_token(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);
        $this->startSession();

        $oldToken = session()->token();

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
    }

    public function test_authenticated_ordinary_user_is_logged_out_when_visiting_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.login'));

        $this->assertGuest();
    }


}
