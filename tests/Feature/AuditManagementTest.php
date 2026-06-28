<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AuditManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_audit_logs_and_non_admins_are_forbidden(): void
    {
        $this->get(route('audit-logs.index'))
            ->assertRedirect(route('login'));

        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_audit_logs_in_descending_created_at_order_by_default(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Viewer',
        ]);

        DB::table('activity_logs')->insert([
            [
                'user_id' => $admin->id,
                'action' => 'create',
                'entity' => 'products',
                'entity_id' => 10,
                'description' => 'Older audit detail',
                'created_at' => now()->subDay(),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'update',
                'entity' => 'products',
                'entity_id' => 11,
                'description' => 'Newest audit detail',
                'created_at' => now(),
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Log Audit')
            ->assertSeeInOrder(['Newest audit detail', 'Older audit detail'], false);
    }

    public function test_admin_can_filter_audit_logs_by_actor_action_entity_and_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $matchingActor = User::factory()->create([
            'name' => 'Alya Admin',
            'username' => 'alya-admin',
        ]);

        $otherActor = User::factory()->create([
            'name' => 'Bima Cashier',
            'username' => 'bima-cashier',
        ]);

        DB::table('activity_logs')->insert([
            [
                'user_id' => $matchingActor->id,
                'action' => 'update',
                'entity' => 'products',
                'entity_id' => 21,
                'description' => 'Matching audit entry',
                'created_at' => '2026-06-01 08:30:00',
            ],
            [
                'user_id' => $matchingActor->id,
                'action' => 'create',
                'entity' => 'products',
                'entity_id' => 22,
                'description' => 'Wrong action entry',
                'created_at' => '2026-06-01 08:30:00',
            ],
            [
                'user_id' => $otherActor->id,
                'action' => 'update',
                'entity' => 'products',
                'entity_id' => 23,
                'description' => 'Wrong actor entry',
                'created_at' => '2026-06-01 08:30:00',
            ],
            [
                'user_id' => $matchingActor->id,
                'action' => 'update',
                'entity' => 'users',
                'entity_id' => 24,
                'description' => 'Wrong entity entry',
                'created_at' => '2026-06-01 08:30:00',
            ],
            [
                'user_id' => $matchingActor->id,
                'action' => 'update',
                'entity' => 'products',
                'entity_id' => 25,
                'description' => 'Wrong date entry',
                'created_at' => '2026-06-02 08:30:00',
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test('audit.audit-list')
            ->set('filters.actor', 'alya')
            ->set('filters.action', 'update')
            ->set('filters.entity', 'products')
            ->set('filters.date', '2026-06-01')
            ->assertSee('Matching audit entry')
            ->assertDontSee('Wrong action entry')
            ->assertDontSee('Wrong actor entry')
            ->assertDontSee('Wrong entity entry')
            ->assertDontSee('Wrong date entry');
    }

    public function test_admin_sees_a_readable_fallback_when_actor_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        DB::table('activity_logs')->insert([
            'user_id' => null,
            'action' => 'sync',
            'entity' => 'inventory',
            'entity_id' => null,
            'description' => 'System generated sync',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Sistem', false)
            ->assertSee('System generated sync');
    }

    public function test_admin_can_see_audit_log_description_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        DB::table('activity_logs')->insert([
            'user_id' => $admin->id,
            'action' => 'delete',
            'entity' => 'expenses',
            'entity_id' => 51,
            'description' => 'Removed duplicate expense entry after nightly reconciliation',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Removed duplicate expense entry after nightly reconciliation');
    }
}
