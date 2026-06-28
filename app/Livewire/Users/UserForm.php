<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'cashier';

    public bool $is_active = true;

    public function mount(?int $userId = null): void
    {
        if (! $userId) {
            return;
        }

        $user = User::findOrFail($userId);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email ?? '';
        $this->role = $user->role;
        $this->is_active = $user->is_active;
    }

    public function save(UserService $userService): void
    {
        $data = $this->validate($this->rules());
        $data['email'] = filled($data['email']) ? $data['email'] : null;

        if ($this->userId) {
            $userService->updateUser($this->userId, $data);
            $message = 'Pengguna berhasil diperbarui.';
        } else {
            $userService->createUser($data);
            $message = 'Pengguna berhasil ditambahkan.';
        }

        $this->dispatch('user-saved', message: $message);
    }

    protected function rules(): array
    {
        $passwordRule = $this->userId ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'];

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:255', Rule::unique(User::class, 'username')->ignore($this->userId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->userId)],
            'password' => $passwordRule,
            'role' => ['required', Rule::in(['admin', 'cashier'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function render()
    {
        return view('livewire.users.user-form');
    }
}
