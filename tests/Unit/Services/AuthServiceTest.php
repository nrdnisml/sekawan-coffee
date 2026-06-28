<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_for_active_user_with_valid_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'cashier-login',
            'is_active' => true,
        ]);

        $result = app(AuthService::class)->login($user->username, 'password');

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'entity' => 'users',
            'entity_id' => $user->id,
        ]);
    }

    public function test_login_fails_for_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'wrong-password-user',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Username atau kata sandi tidak valid.');

        app(AuthService::class)->login($user->username, 'invalid-password');
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $user = User::factory()->create([
            'username' => 'inactive-service-user',
            'is_active' => false,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Akun sedang tidak aktif.');

        app(AuthService::class)->login($user->username, 'password');
    }

    public function test_logout_writes_audit_log_and_logs_user_out(): void
    {
        $user = User::factory()->create([
            'username' => 'logout-service-user',
        ]);

        $this->actingAs($user);

        app(AuthService::class)->logout();

        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
            'entity' => 'users',
            'entity_id' => $user->id,
        ]);
        $this->assertSame(1, ActivityLog::query()->count());
    }
}
