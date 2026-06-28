<?php

namespace Tests\Feature;

use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_users_page(): void
    {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_users_list_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna');
    }

    public function test_cashier_is_forbidden_from_the_users_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user_via_the_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'Kasir Pagi')
            ->set('username', 'kasir-pagi')
            ->set('email', 'kasir-pagi@example.com')
            ->set('password', 'password')
            ->set('role', 'cashier')
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('user-saved');

        $this->assertDatabaseHas('users', [
            'username' => 'kasir-pagi',
            'role' => 'cashier',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_edit_a_user_via_the_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'username' => 'old-cashier',
            'name' => 'Old Cashier',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(UserForm::class, ['userId' => $cashier->id])
            ->set('name', 'Updated Cashier')
            ->set('username', 'updated-cashier')
            ->set('email', 'updated-cashier@example.com')
            ->set('password', '')
            ->set('role', 'cashier')
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('user-saved');

        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'name' => 'Updated Cashier',
            'username' => 'updated-cashier',
        ]);
    }

    public function test_admin_can_deactivate_a_user_from_the_list(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->call('toggleStatus', $cashier->id)
            ->assertHasNoErrors();

        $this->assertFalse($cashier->fresh()->is_active);
    }

    public function test_admin_can_delete_another_user_from_the_list(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->set('userToDelete', $cashier->id)
            ->call('deleteUser')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('users', [
            'id' => $cashier->id,
        ]);
    }

    public function test_admin_can_not_delete_their_own_account_from_the_list(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->set('userToDelete', $admin->id)
            ->call('deleteUser')
            ->assertDispatched('toast-show');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_filter_users_by_search_and_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'primary-admin',
            'name' => 'Primary Admin',
        ]);

        $visibleUser = User::factory()->create([
            'role' => 'cashier',
            'username' => 'kasir-pagi',
            'name' => 'Kasir Pagi',
            'is_active' => true,
        ]);

        User::factory()->create([
            'role' => 'cashier',
            'username' => 'kasir-malam',
            'name' => 'Kasir Malam',
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->set('filters.search', 'pagi')
            ->set('filters.status', '1')
            ->assertSee($visibleUser->name)
            ->assertSee($visibleUser->username)
            ->assertDontSee('Kasir Malam');
    }
}
