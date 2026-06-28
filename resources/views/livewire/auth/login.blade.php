<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth.root-login', ['title' => 'Sekawan Coffee — Login', 'metaDescription' => 'Masuk ke Sekawan Coffee untuk mengelola pesanan, stok, dan operasional toko.'])] class extends Component
{
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(AuthService $authService): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        try {
            $authService->login($this->username, $this->password);
        } catch (Exception $exception) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => $exception->getMessage(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Handle a development-only quick login.
     */
    public function devLogin(string $role): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $email = $role === 'admin' ? 'admin@sekawan.com' : 'cashier@sekawan.com';
        $username = $role === 'admin' ? 'admin' : 'cashier';
        $name = $role === 'admin' ? 'Admin Sekawan' : 'Cashier Sekawan';

        $user = User::where('username', $username)->first();

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'is_active' => true,
            ]);
        }

        if ($user) {
            Auth::login($user);
            Session::regenerate();
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }
}; ?>

<div class="login-form-stack flex flex-col gap-6">
    <div class="login-form-intro flex w-full flex-col gap-2 text-center">
        <h1>Masuk ke akun Anda</h1>
        <p>Masukkan username dan kata sandi Anda untuk masuk</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="login-session-status text-center" :status="session('status')" />

    <form wire:submit="login" class="login-form-grid flex flex-col gap-6">
        <flux:input wire:model="username" label="Username" type="text" name="username" required autofocus autocomplete="username" placeholder="your.username" />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                label="{{ __('Kata Sandi') }}"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Kata sandi"
            />

            @if (Route::has('password.request'))
                <a class="absolute right-0 top-0 text-sm font-medium text-[var(--login-accent-primary)] hover:text-[var(--login-accent-hover)]" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Lupa kata sandi?') }}
                </a>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="{{ __('Ingat saya') }}" />

        <div class="flex items-center justify-end mt-2">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Masuk') }}</flux:button>
        </div>

        @if (app()->isLocal())
            <div class="login-dev-strip flex flex-col gap-3 border-t pt-4">
                <p class="text-center text-[0.6875rem] font-semibold uppercase tracking-[0.08em]">Login Cepat Dev</p>
                <div class="flex gap-3">
                    <flux:button wire:click="devLogin('admin')" variant="subtle" class="flex-1 !bg-[var(--login-surface-muted)] !text-[var(--login-text-primary)] hover:!bg-[var(--login-surface-secondary)] border-none">Admin</flux:button>
                    <flux:button wire:click="devLogin('cashier')" variant="subtle" class="flex-1 !bg-[var(--login-surface-muted)] !text-[var(--login-text-primary)] hover:!bg-[var(--login-surface-secondary)] border-none">Cashier</flux:button>
                </div>
            </div>
        @endif
    </form>

    <div class="login-register-prompt space-x-1 text-center text-sm">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-[var(--login-accent-primary)] hover:text-[var(--login-accent-hover)]" wire:navigate>Daftar</a>
    </div>
</div>
