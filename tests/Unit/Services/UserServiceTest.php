<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_and_password_is_hashed(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $user = app(UserService::class)->createUser([
            'name' => 'Cashier Baru',
            'username' => 'cashier-baru',
            'email' => 'cashier-baru@example.com',
            'password' => 'password123',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->assertSame('cashier-baru', $user->username);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'create',
            'entity' => 'users',
            'entity_id' => $user->id,
        ]);
    }

    public function test_non_admin_can_not_create_user(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tidak diizinkan. Hanya admin yang dapat mengelola pengguna.');

        app(UserService::class)->createUser([
            'name' => 'Invalid User',
            'username' => 'invalid-user',
            'email' => 'invalid@example.com',
            'password' => 'password123',
        ]);
    }

    public function test_admin_can_update_user_without_overwriting_password_when_blank(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create([
            'username' => 'existing-user',
            'password' => Hash::make('original-password'),
        ]);

        $this->actingAs($admin);

        $updatedUser = app(UserService::class)->updateUser($user->id, [
            'name' => 'Updated User',
            'username' => 'existing-user',
            'email' => '',
            'password' => '',
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $this->assertSame('Updated User', $updatedUser->name);
        $this->assertNull($updatedUser->email);
        $this->assertFalse($updatedUser->is_active);
        $this->assertTrue(Hash::check('original-password', $updatedUser->fresh()->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'update',
            'entity' => 'users',
            'entity_id' => $user->id,
        ]);
    }

    public function test_admin_can_not_delete_their_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Anda tidak dapat menghapus akun sendiri.');

        app(UserService::class)->deleteUser($admin->id);
    }

    public function test_admin_can_soft_delete_another_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $this->actingAs($admin);

        app(UserService::class)->deleteUser($cashier->id);

        $this->assertSoftDeleted('users', [
            'id' => $cashier->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'delete',
            'entity' => 'users',
            'entity_id' => $cashier->id,
        ]);
        $this->assertGreaterThanOrEqual(1, ActivityLog::query()->count());
    }
}
