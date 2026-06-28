<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_inserts_an_activity_log_record(): void
    {
        $user = User::factory()->create();

        app(AuditService::class)->log($user->id, 'create', 'products', 34, 'Created product: Espresso');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'create',
            'entity' => 'products',
            'entity_id' => 34,
            'description' => 'Created product: Espresso',
        ]);
    }

    public function test_it_supports_nullable_users(): void
    {
        app(AuditService::class)->log(null, 'sync', 'inventory', null, 'Nightly stock sync completed');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'action' => 'sync',
            'entity' => 'inventory',
            'entity_id' => null,
            'description' => 'Nightly stock sync completed',
        ]);
    }

    public function test_it_fails_safe_when_persistence_throws(): void
    {
        Log::spy();
        Schema::drop('activity_logs');

        app(AuditService::class)->log(7, 'delete', 'users', 9, 'Deleted user: cashier');

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message): bool => str_contains($message, 'Failed to create activity log:'));
    }
}
