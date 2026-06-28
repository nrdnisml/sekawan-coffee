<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt as LivewireVolt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_login_screen_can_be_rendered_for_guests(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSeeText('Masuk ke akun Anda')
            ->assertSee(asset('assets/img/logo.jpeg'), false);

        $this->assertSame('/', route('login', absolute: false));
    }

    public function test_legacy_login_route_redirects_to_root_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/');
    }

    public function test_authenticated_users_are_redirected_away_from_guest_login_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_username_on_the_login_screen(): void
    {
        $user = User::factory()->create([
            'username' => 'cashier01',
        ]);

        $response = LivewireVolt::test('auth.login')
            ->set('username', $user->username)
            ->set('password', 'password')
            ->call('login');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_inactive_users_can_not_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'username' => 'inactive-cashier',
            'is_active' => false,
        ]);

        LivewireVolt::test('auth.login')
            ->set('username', $user->username)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'admin-user',
        ]);

        LivewireVolt::test('auth.login')
            ->set('username', $user->username)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['username']);

        $this->assertGuest();
    }

    public function test_users_can_logout_and_the_action_is_audited(): void
    {
        $user = User::factory()->create([
            'username' => 'logout-user',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');

        $this->assertDatabaseHas(ActivityLog::class, [
            'user_id' => $user->id,
            'action' => 'logout',
            'entity' => 'users',
            'entity_id' => $user->id,
        ]);
    }
}
