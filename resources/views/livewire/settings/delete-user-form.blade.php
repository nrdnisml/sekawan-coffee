<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $this->redirect('/', navigate: true);

            return;
        }

        $user->delete();
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Hapus Akun') }}</flux:heading>
        <flux:subheading>{{ __('Hapus akun Anda beserta semua datanya') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Hapus Akun') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Apakah Anda yakin ingin menghapus akun ini?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Setelah akun dihapus, semua sumber daya dan data yang terkait akan ikut terhapus. Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" id="password" label="{{ __('Kata Sandi') }}" type="password" name="password" />

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Batal') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Hapus Akun') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
