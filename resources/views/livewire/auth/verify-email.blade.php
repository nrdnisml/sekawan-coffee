<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout(app(AuthService::class));

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <div class="text-center text-sm text-gray-600">
        {{ __('Silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="font-medium text-center text-sm text-green-600">
            {{ __('Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.') }}
        </div>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Kirim ulang email verifikasi') }}
        </flux:button>

        <button
            wire:click="logout"
            type="submit"
            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            {{ __('Keluar') }}
        </button>
    </div>
</div>
