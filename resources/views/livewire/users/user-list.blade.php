<div class="relative">
    <div wire:loading class="fixed top-0 left-0 right-0 h-0.5 z-[999] overflow-hidden pointer-events-none">
        <div class="h-full bg-indigo-600 w-full animate-progress-bar shadow-[0_0_10px_rgba(79,70,229,0.5)]"></div>
    </div>

    <style>
        @keyframes progress-bar {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-progress-bar {
            animation: progress-bar 2s infinite linear;
        }
    </style>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">
            Manajemen Pengguna
        </h1>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="funnel" wire:click="toggleFilters">
                {{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
            </flux:button>

            <flux:button variant="filled" color="accent" icon="plus" wire:click="openAddModal">
                Tambah Pengguna
            </flux:button>
        </div>
    </div>

    @if ($showFilters)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-5 mb-6 bg-zinc-50 border border-zinc-200 rounded-xl dark:bg-zinc-900 dark:border-zinc-800 items-center">
            <flux:input wire:model.live.debounce.300ms="filters.search" placeholder="Cari nama, username, email..." icon="magnifying-glass" />

            <flux:select wire:model.live="filters.role" placeholder="Semua Peran">
                <flux:select.option value="">Semua Peran</flux:select.option>
                <flux:select.option value="admin">Admin</flux:select.option>
                <flux:select.option value="cashier">Kasir</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filters.status" placeholder="Semua Status">
                <flux:select.option value="">Semua Status</flux:select.option>
                <flux:select.option value="1">Aktif</flux:select.option>
                <flux:select.option value="0">Nonaktif</flux:select.option>
            </flux:select>

            <div>
                @if (filled($filters['search']) || filled($filters['role']) || filled($filters['status']))
                    <flux:button variant="subtle" color="danger" icon="x-mark" wire:click="clearAllFilters" class="w-full justify-center">
                        Bersihkan Filter
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
        <flux:table variant="striped">
            <flux:table.columns>
                <flux:table.column sortable :direction="$sortField === 'name' ? $sortDirection : null" wire:click="sortBy('name')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Nama
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'username' ? $sortDirection : null" wire:click="sortBy('username')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Username
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'role' ? $sortDirection : null" wire:click="sortBy('role')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Peran
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'is_active' ? $sortDirection : null" wire:click="sortBy('is_active')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Status
                </flux:table.column>
                <flux:table.column align="end" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Aksi
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="py-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $user->email ?: 'Email belum diisi' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                            {{ $user->username }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="sky" size="sm" inset="top bottom">
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="{{ $user->is_active ? 'green' : 'zinc' }}" size="sm" inset="top bottom" class="cursor-pointer" wire:click="toggleStatus({{ $user->id }})">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button icon="pencil-square" size="sm" variant="subtle" square wire:click="editUser({{ $user->id }})" />

                                <flux:button icon="trash" size="sm" variant="subtle" square class="hover:text-red-600 dark:hover:text-red-400" wire:click="confirmDelete({{ $user->id }})" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <flux:icon.magnifying-glass class="size-8 mb-2 text-zinc-300" />
                                <p class="text-sm font-medium">Tidak ada pengguna yang cocok dengan filter Anda.</p>
                                <flux:button variant="subtle" size="sm" class="mt-2" wire:click="clearAllFilters">
                                    Bersihkan semua filter
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <flux:modal name="user-form-modal" class="min-w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingUserId ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</flux:heading>
                <flux:subheading>Kelola username, hak akses, dan status akun.</flux:subheading>
            </div>

            <livewire:users.user-form :user-id="$editingUserId" :key="'user-form-' . ($editingUserId ?? 'new')" />
        </div>
    </flux:modal>

    <flux:modal name="delete-user-modal" class="min-w-[25rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Pengguna?</flux:heading>
                <flux:subheading>
                    <p>Apakah Anda yakin ingin menghapus pengguna ini? <strong>Tindakan ini tidak dapat dibatalkan.</strong></p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button color="danger" wire:click="deleteUser">Hapus Pengguna</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
