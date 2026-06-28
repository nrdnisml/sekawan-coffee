<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Kasir Baru')
            ->set('username', 'kasir-baru')
            ->set('email', 'kasir-baru@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Kasir Baru',
            'username' => 'kasir-baru',
            'email' => 'kasir-baru@example.com',
            'role' => 'cashier',
            'is_active' => true,
        ]);
    }

    public function test_registration_requires_unique_username(): void
    {
        User::factory()->create([
            'username' => 'duplicate-user',
        ]);

        Volt::test('auth.register')
            ->set('name', 'Kasir Baru')
            ->set('username', 'duplicate-user')
            ->set('email', 'duplicate-user@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['username']);
    }
}
