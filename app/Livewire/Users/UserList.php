<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Services\UserService;
use Flux\Concerns\InteractsWithComponents;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class UserList extends Component
{
    use InteractsWithComponents;
    use WithPagination;

    public ?int $editingUserId = null;

    public ?int $userToDelete = null;

    #[Url(history: true)]
    public string $sortField = 'created_at';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public array $filters = [
        'search' => '',
        'role' => '',
        'status' => '',
    ];

    public bool $showFilters = false;

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function updating(string $property): void
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function clearAllFilters(): void
    {
        $this->reset('filters');
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function openAddModal(): void
    {
        $this->editingUserId = null;
        $this->js('$flux.modal("user-form-modal").show()');
    }

    public function editUser(int $userId): void
    {
        $this->editingUserId = $userId;
        $this->js('$flux.modal("user-form-modal").show()');
    }

    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
        $this->js('$flux.modal("delete-user-modal").show()');
    }

    public function toggleStatus(int $userId, UserService $userService): void
    {
        $user = User::findOrFail($userId);

        $userService->updateUser($userId, [
            'is_active' => ! $user->is_active,
        ]);

        $this->toast(
            heading: 'Status Diperbarui',
            text: "Status {$user->name} berhasil diubah.",
            variant: 'success',
        );
    }

    public function deleteUser(UserService $userService): void
    {
        if (! $this->userToDelete) {
            return;
        }

        try {
            $user = User::findOrFail($this->userToDelete);
            $userName = $user->name;
            $userService->deleteUser($this->userToDelete);

            $this->toast(
                heading: 'Pengguna Dihapus',
                text: "{$userName} telah dihapus dari daftar pengguna.",
                variant: 'success',
            );
        } catch (\Exception $exception) {
            $this->toast(
                heading: 'Gagal',
                text: $exception->getMessage(),
                variant: 'danger',
            );
        }

        $this->js('$flux.modal("delete-user-modal").close()');
        $this->userToDelete = null;
    }

    #[On('user-saved')]
    public function handleUserSaved(string $message): void
    {
        $this->js('$flux.modal("user-form-modal").close()');

        $this->toast(
            heading: 'Berhasil',
            text: $message,
            variant: 'success',
        );
    }

    public function render()
    {
        $users = User::query()
            ->when($this->filters['search'], function ($query) {
                $query->where(function ($userQuery) {
                    $search = '%'.$this->filters['search'].'%';

                    $userQuery
                        ->where('name', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($this->filters['role'], fn ($query) => $query->where('role', $this->filters['role']))
            ->when($this->filters['status'] !== '', fn ($query) => $query->where('is_active', (bool) $this->filters['status']))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.users.user-list', [
            'users' => $users,
        ]);
    }
}
